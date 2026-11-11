<?php

namespace App\Services;

use App\Models\CompanySetting;
use App\Models\Customer;
use App\Models\Fleet;
use App\Models\Shipment;
use App\Models\TrackingLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ShipmentDomainService
{
    public const STAGE_LABELS = [
        1 => 'St. 1: Pickup / Barang Dijemput',
        2 => 'St. 2: Proses Hub / Sortir',
        3 => 'St. 3: Transit / Dalam Perjalanan',
        4 => 'St. 4: Out for Delivery / Kurir Menuju Lokasi',
        5 => 'St. 5: Delivered / Diterima & POD',
    ];

    public const PAYMENT_STATUS_OPTIONS = [
        'Belum Lunas',
        'DP / Sebagian',
        'Tagihan TOP 7 Hari',
        'Tagihan TOP 14 Hari',
        'Tagihan TOP 30 Hari',
        'Lunas',
    ];

    public function localIsoDate(): string
    {
        return now('Asia/Jakarta')->format('Y-m-d');
    }

    public function localDateTimeStr(): string
    {
        return now('Asia/Jakarta')->format('Y-m-d H:i:s');
    }

    public function shortWibTimestamp(): string
    {
        return now('Asia/Jakarta')->format('d M H:i').' WIB';
    }

    public function generateNextCnNumber(): string
    {
        $prefix = CompanySetting::query()->value('cn_prefix') ?: 'AACL';
        $ym = now('Asia/Jakarta')->format('ym');
        $pattern = "{$prefix}-{$ym}-";

        $existing = Shipment::query()
            ->where('cn_number', 'like', $pattern.'%')
            ->pluck('cn_number');

        $maxSeq = 0;
        foreach ($existing as $cn) {
            if (preg_match('/^'.preg_quote($pattern, '/').'(\d+)$/', (string) $cn, $m)) {
                $num = (int) $m[1];
                if ($num > $maxSeq) {
                    $maxSeq = $num;
                }
            }
        }

        return sprintf('%s%04d', $pattern, $maxSeq + 1);
    }

    public function computeCurrentStage(array|Shipment $data): int
    {
        $highest = 0;
        for ($i = 1; $i <= 5; $i++) {
            $field = "status_{$i}_at";
            $val = is_array($data) ? ($data[$field] ?? null) : $data->{$field};
            if (filled($val)) {
                $highest = $i;
            }
        }

        return $highest;
    }

    public function removeOldPodPhoto(?string $photoUrl): void
    {
        if (! $photoUrl || ! str_starts_with($photoUrl, '/uploads/pod/')) {
            return;
        }

        $uploadRoot = realpath(public_path('uploads/pod'));
        if (! $uploadRoot) {
            return;
        }

        $relative = substr($photoUrl, strlen('/uploads/pod/'));
        $relative = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($relative, '/\\'));
        $target = realpath($uploadRoot.DIRECTORY_SEPARATOR.$relative);

        if ($target && str_starts_with($target, $uploadRoot.DIRECTORY_SEPARATOR) && is_file($target)) {
            @unlink($target);
        }
    }

    public function savePodPhotoBase64(string $dataUrl, string $cnNumber): ?string
    {
        if (! preg_match('/^data:image\/(png|jpeg|jpg|webp);base64,(.+)$/is', trim($dataUrl), $matches)) {
            return null;
        }

        $ext = strtolower($matches[1]) === 'jpeg' ? 'jpg' : strtolower($matches[1]);
        $binary = base64_decode($matches[2], true);
        if ($binary === false || strlen($binary) === 0) {
            return null;
        }

        $dir = public_path('uploads/pod');
        File::ensureDirectoryExists($dir);

        $safeCn = preg_replace('/[^A-Za-z0-9\-]/', '_', $cnNumber);
        $filename = 'POD_'.$safeCn.'_'.now('Asia/Jakarta')->format('YmdHis').'_'.substr(md5((string) microtime(true)), 0, 4).'.'.$ext;
        file_put_contents($dir.DIRECTORY_SEPARATOR.$filename, $binary);

        return '/uploads/pod/'.$filename;
    }

    public function savePodPhotoUpload(UploadedFile $file, string $cnNumber): string
    {
        $dir = public_path('uploads/pod');
        File::ensureDirectoryExists($dir);

        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        if (! in_array($ext, ['png', 'jpg', 'jpeg', 'webp'], true)) {
            $ext = 'jpg';
        }
        if ($ext === 'jpeg') {
            $ext = 'jpg';
        }

        $safeCn = preg_replace('/[^A-Za-z0-9\-]/', '_', $cnNumber);
        $filename = 'POD_'.$safeCn.'_'.now('Asia/Jakarta')->format('YmdHis').'_'.substr(md5((string) microtime(true)), 0, 4).'.'.$ext;
        $file->move($dir, $filename);

        return '/uploads/pod/'.$filename;
    }

    public function resolveOrCreateCustomer(
        ?int $customerId,
        string $customerName,
        ?string $shipperAddress = null,
        bool $saveToMaster = false
    ): ?Customer {
        if ($customerId) {
            $found = Customer::find($customerId);
            if ($found) {
                return $found;
            }
        }

        $trimmed = trim($customerName);
        if ($trimmed === '') {
            return null;
        }

        $existing = Customer::query()
            ->whereRaw('LOWER(company_name) = ?', [mb_strtolower($trimmed)])
            ->first();

        if ($existing) {
            return $existing;
        }

        if (! $saveToMaster) {
            return null;
        }

        $maxNum = 0;
        foreach (Customer::query()->pluck('customer_code') as $code) {
            if (preg_match('/^CUST-(\d+)$/i', (string) $code, $m)) {
                $maxNum = max($maxNum, (int) $m[1]);
            }
        }

        return Customer::create([
            'customer_code' => sprintf('CUST-%03d', $maxNum + 1),
            'company_name' => $trimmed,
            'default_address' => $shipperAddress,
            'notes' => 'Ditambahkan otomatis saat input CN',
        ]);
    }

    public function generateNextCustomerCode(): string
    {
        $maxNum = 0;
        foreach (Customer::query()->pluck('customer_code') as $code) {
            if (preg_match('/^CUST-(\d+)$/i', (string) $code, $m)) {
                $maxNum = max($maxNum, (int) $m[1]);
            }
        }

        return sprintf('CUST-%03d', $maxNum + 1);
    }

    public function generateNextFleetCode(): string
    {
        $maxNum = 0;
        foreach (Fleet::query()->pluck('fleet_code') as $code) {
            if (preg_match('/^FLT-(\d+)$/i', (string) $code, $m)) {
                $maxNum = max($maxNum, (int) $m[1]);
            }
        }

        return sprintf('FLT-%03d', $maxNum + 1);
    }

    public function filterShipmentsQuery(array $filters = []): Builder
    {
        $query = Shipment::query()->orderByDesc('shipment_date')->orderByDesc('id');

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $like = '%'.$search.'%';
            $query->where(function (Builder $q) use ($like) {
                $q->where('cn_number', 'like', $like)
                    ->orWhere('customer_name', 'like', $like)
                    ->orWhere('shipper_address', 'like', $like)
                    ->orWhere('receiver_address', 'like', $like)
                    ->orWhere('fleet_name', 'like', $like)
                    ->orWhere('notes', 'like', $like);
            });
        }

        $stage = (string) ($filters['stage'] ?? 'all');
        if ($stage !== 'all' && $stage !== '' && is_numeric($stage)) {
            $query->where('current_stage', (int) $stage);
        }

        $dateFrom = trim((string) ($filters['date_from'] ?? ''));
        if ($dateFrom !== '') {
            $query->where('shipment_date', '>=', $dateFrom);
        }

        $dateTo = trim((string) ($filters['date_to'] ?? ''));
        if ($dateTo !== '') {
            $query->where('shipment_date', '<=', $dateTo);
        }

        $paymentStatus = trim((string) ($filters['payment_status'] ?? 'all'));
        if ($paymentStatus === 'unpaid_all') {
            $query->where('payment_status', '!=', 'Lunas');
        } elseif ($paymentStatus !== '' && $paymentStatus !== 'all') {
            $query->where('payment_status', $paymentStatus);
        }

        return $query;
    }

    public function computeStats(Collection $shipments): array
    {
        $stageCounts = [0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        $totalColly = 0;
        $totalWeight = 0.0;
        $totalVolume = 0.0;
        $totalArea = 0.0;
        $totalRevenue = 0.0;
        $paidRevenue = 0.0;

        foreach ($shipments as $s) {
            $st = max(0, min(5, (int) $s->current_stage));
            $stageCounts[$st]++;
            $totalColly += (int) $s->colly;
            $totalWeight += (float) $s->weight_kg;
            $totalVolume += (float) $s->volume_m3;
            $totalArea += (float) $s->area_m2;
            $cost = (float) $s->service_cost;
            $totalRevenue += $cost;
            if ($s->payment_status === 'Lunas') {
                $paidRevenue += $cost;
            }
        }

        return [
            'total_cn' => $shipments->count(),
            'total_colly' => $totalColly,
            'total_weight' => round($totalWeight, 2),
            'total_volume' => round($totalVolume, 3),
            'total_area' => round($totalArea, 2),
            'in_progress' => $stageCounts[0] + $stageCounts[1] + $stageCounts[2] + $stageCounts[3] + $stageCounts[4],
            'delivered' => $stageCounts[5],
            'total_revenue' => $totalRevenue,
            'paid_revenue' => $paidRevenue,
            'unpaid_revenue' => max(0, $totalRevenue - $paidRevenue),
            'stage_counts' => $stageCounts,
        ];
    }

    public function updateShipmentStage(Shipment $shipment, int $stage, ?string $value, string $updatedBy): Shipment
    {
        $field = "status_{$stage}_at";
        $trimmed = $value !== null && trim($value) !== '' ? trim($value) : null;

        $shipment->{$field} = $trimmed;
        $shipment->current_stage = $this->computeCurrentStage($shipment);
        $shipment->save();

        TrackingLog::create([
            'shipment_id' => $shipment->id,
            'stage_number' => $stage,
            'stage_label' => self::STAGE_LABELS[$stage] ?? "Status {$stage}",
            'description' => $trimmed ?: '(Status dikosongkan)',
            'updated_by' => $updatedBy,
            'created_at' => now('Asia/Jakarta'),
        ]);

        return $shipment->fresh();
    }

    public function updateShipmentPod(
        Shipment $shipment,
        array $podData,
        ?UploadedFile $photoUpload,
        string $updatedBy
    ): Shipment {
        $oldPhotoUrl = $shipment->pod_photo_url;
        $newPhotoUrl = $oldPhotoUrl;

        if ($photoUpload) {
            $newPhotoUrl = $this->savePodPhotoUpload($photoUpload, $shipment->cn_number);
            if ($oldPhotoUrl && $oldPhotoUrl !== $newPhotoUrl) {
                $this->removeOldPodPhoto($oldPhotoUrl);
            }
        } elseif (! empty($podData['pod_photo_base64'])) {
            $saved = $this->savePodPhotoBase64($podData['pod_photo_base64'], $shipment->cn_number);
            if ($saved) {
                $newPhotoUrl = $saved;
                if ($oldPhotoUrl && $oldPhotoUrl !== $newPhotoUrl) {
                    $this->removeOldPodPhoto($oldPhotoUrl);
                }
            }
        }

        $receiverName = trim((string) ($podData['pod_receiver_name'] ?? ''));
        $receivedAt = trim((string) ($podData['pod_received_at'] ?? '')) ?: $this->localDateTimeStr();
        $signatureData = $podData['pod_signature_data'] ?? $shipment->pod_signature_data;
        if (($podData['clear_signature'] ?? false) === true) {
            $signatureData = null;
        }
        $podNotes = trim((string) ($podData['pod_notes'] ?? ''));

        $shipment->pod_receiver_name = $receiverName !== '' ? $receiverName : null;
        $shipment->pod_received_at = $receivedAt;
        $shipment->pod_photo_url = $newPhotoUrl;
        $shipment->pod_signature_data = $signatureData ?: null;
        $shipment->pod_notes = $podNotes !== '' ? $podNotes : null;

        // Auto-sync Status 5 when POD is filled and Status 5 is empty
        if (blank($shipment->status_5_at) && $shipment->hasPod()) {
            $who = $shipment->pod_receiver_name ? "Diterima oleh {$shipment->pod_receiver_name}" : 'Diterima & POD Tersimpan';
            $shipment->status_5_at = $this->shortWibTimestamp().' - '.$who;
        }

        $shipment->current_stage = $this->computeCurrentStage($shipment);
        $shipment->save();

        TrackingLog::create([
            'shipment_id' => $shipment->id,
            'stage_number' => 5,
            'stage_label' => self::STAGE_LABELS[5],
            'description' => trim("POD disimpan. Penerima: ".($shipment->pod_receiver_name ?: '-').($shipment->pod_notes ? " | {$shipment->pod_notes}" : '')),
            'updated_by' => $updatedBy,
            'created_at' => now('Asia/Jakarta'),
        ]);

        return $shipment->fresh();
    }

    public function deleteShipment(Shipment $shipment): void
    {
        DB::transaction(function () use ($shipment) {
            $this->removeOldPodPhoto($shipment->pod_photo_url);
            $shipment->trackingLogs()->delete();
            $shipment->delete();
        });
    }

    public function sanitizePublicTracking(Shipment $shipment): array
    {
        $logs = $shipment->trackingLogs()->get()->map(fn (TrackingLog $log) => [
            'stage_number' => $log->stage_number,
            'stage_label' => $log->stage_label,
            'description' => $log->description,
            'updated_by' => $log->updated_by,
            'created_at' => optional($log->created_at)->format('Y-m-d H:i:s'),
        ])->values()->all();

        return [
            'cn_number' => $shipment->cn_number,
            'shipment_date' => $shipment->shipment_date,
            'customer_name' => $shipment->customer_name,
            'shipper_address' => $shipment->shipper_address,
            'receiver_address' => $shipment->receiver_address,
            'colly' => $shipment->colly,
            'weight_kg' => $shipment->weight_kg,
            'volume_m3' => $shipment->volume_m3,
            'area_m2' => $shipment->area_m2,
            'fleet_name' => $shipment->fleet_name,
            'pickup_time' => $shipment->pickup_time,
            'status_1_at' => $shipment->status_1_at,
            'status_2_at' => $shipment->status_2_at,
            'status_3_at' => $shipment->status_3_at,
            'status_4_at' => $shipment->status_4_at,
            'status_5_at' => $shipment->status_5_at,
            'pod_receiver_name' => $shipment->pod_receiver_name,
            'pod_received_at' => $shipment->pod_received_at,
            'pod_photo_url' => $shipment->pod_photo_url,
            'pod_signature_data' => $shipment->pod_signature_data,
            'pod_notes' => $shipment->pod_notes,
            'current_stage' => $shipment->current_stage,
            'notes' => $shipment->notes,
            'logs' => $logs,
        ];
    }
}
