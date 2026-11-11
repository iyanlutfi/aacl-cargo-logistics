<?php

namespace App\Services;

use App\Models\Fleet;
use App\Models\Shipment;
use App\Models\TrackingLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CsvService
{
    public function exportCsv(Collection $shipments): string
    {
        $row1 = [
            'No. CN', 'Tanggal', 'Nama Cust', 'Alamat Pengirim', 'Alamat Penerima',
            'Jumlah', '', '', '',
            'Armada', 'Pickup',
            'Status', '', '', '', '',
            'Bukti Diterima (POD)', 'Harga Kirim (Rp)', 'Status Bayar', 'Catatan',
        ];
        $row2 = [
            '', '', '', '', '',
            'Colly', 'Kg', 'Vol (m³)', 'm²',
            '', '',
            '1', '2', '3', '4', '5',
            '', '', '', '',
        ];

        $lines = [
            $this->toCsvRow($row1),
            $this->toCsvRow($row2),
        ];

        foreach ($shipments as $s) {
            $podSummary = '';
            if ($s->pod_receiver_name || $s->pod_received_at || $s->pod_notes) {
                $podSummary = implode(' | ', array_filter([
                    $s->pod_receiver_name ? 'Penerima: '.$s->pod_receiver_name : null,
                    $s->pod_received_at ? 'Waktu: '.$s->pod_received_at : null,
                    $s->pod_notes ?: null,
                ]));
            }

            $lines[] = $this->toCsvRow([
                $s->cn_number,
                $s->shipment_date,
                $s->customer_name,
                $s->shipper_address ?? '',
                $s->receiver_address ?? '',
                (string) (int) $s->colly,
                (string) (float) $s->weight_kg,
                (string) (float) $s->volume_m3,
                (string) (float) $s->area_m2,
                $s->fleet_name ?? '',
                $s->pickup_time ?? '',
                $s->status_1_at ?? '',
                $s->status_2_at ?? '',
                $s->status_3_at ?? '',
                $s->status_4_at ?? '',
                $s->status_5_at ?? '',
                $podSummary,
                (string) (float) $s->service_cost,
                $s->payment_status ?: 'Belum Lunas',
                $s->notes ?? '',
            ]);
        }

        return "\xEF\xBB\xBF".implode("\r\n", $lines)."\r\n";
    }

    public function parseCsvRecords(string $csvText): array
    {
        $text = preg_replace('/^\xEF\xBB\xBF/', '', $csvText);
        $records = [];
        $row = [];
        $field = '';
        $inQuotes = false;
        $len = strlen($text);

        for ($i = 0; $i < $len; $i++) {
            $ch = $text[$i];
            if ($inQuotes) {
                if ($ch === '"') {
                    if ($i + 1 < $len && $text[$i + 1] === '"') {
                        $field .= '"';
                        $i++;
                    } else {
                        $inQuotes = false;
                    }
                } else {
                    $field .= $ch;
                }
            } else {
                if ($ch === '"') {
                    $inQuotes = true;
                } elseif ($ch === ',') {
                    $row[] = $field;
                    $field = '';
                } elseif ($ch === "\r") {
                    if ($i + 1 < $len && $text[$i + 1] === "\n") {
                        $i++;
                    }
                    $row[] = $field;
                    $records[] = $row;
                    $row = [];
                    $field = '';
                } elseif ($ch === "\n") {
                    $row[] = $field;
                    $records[] = $row;
                    $row = [];
                    $field = '';
                } else {
                    $field .= $ch;
                }
            }
        }

        if ($field !== '' || count($row) > 0) {
            $row[] = $field;
            $records[] = $row;
        }

        return array_values(array_filter($records, function ($r) {
            foreach ($r as $cell) {
                if (trim((string) $cell) !== '') {
                    return true;
                }
            }

            return false;
        }));
    }

    public function importCsv(string $csvText, ShipmentDomainService $domain, string $updatedBy): array
    {
        $records = $this->parseCsvRecords($csvText);
        $inserted = 0;
        $updated = 0;
        $skipped = 0;
        $allFleets = Fleet::all();

        DB::transaction(function () use ($records, $domain, $updatedBy, $allFleets, &$inserted, &$updated, &$skipped) {
            foreach ($records as $index => $row) {
                $col0 = trim((string) ($row[0] ?? ''));
                $col5 = trim((string) ($row[5] ?? ''));

                if (strcasecmp($col0, 'No. CN') === 0 || ($index <= 1 && $col0 === '' && strcasecmp($col5, 'Colly') === 0)) {
                    continue;
                }

                $customerName = trim((string) ($row[2] ?? ''));
                if ($col0 === '' && $customerName === '') {
                    $skipped++;
                    continue;
                }

                $cn = $col0 !== '' ? strtoupper($col0) : $domain->generateNextCnNumber();
                $shipperAddress = trim((string) ($row[3] ?? ''));
                $customer = $domain->resolveOrCreateCustomer(null, $customerName ?: 'Customer Import', $shipperAddress, true);

                $fleetName = trim((string) ($row[9] ?? ''));
                $fleetId = null;
                if ($fleetName !== '') {
                    foreach ($allFleets as $f) {
                        if (stripos($fleetName, $f->plate_number) !== false || strcasecmp($fleetName, "{$f->vehicle_type} ({$f->plate_number})") === 0) {
                            $fleetId = $f->id;
                            break;
                        }
                    }
                }

                $rawPod = trim((string) ($row[16] ?? ''));
                $podReceiver = null;
                $podReceivedAt = null;
                $podNotes = null;
                if ($rawPod !== '') {
                    $parts = array_map('trim', explode('|', $rawPod));
                    $noteParts = [];
                    foreach ($parts as $p) {
                        if (preg_match('/^Penerima:\s*(.+)$/i', $p, $m)) {
                            $podReceiver = trim($m[1]);
                        } elseif (preg_match('/^Waktu:\s*(.+)$/i', $p, $m)) {
                            $podReceivedAt = trim($m[1]);
                        } elseif ($p !== '') {
                            $noteParts[] = $p;
                        }
                    }
                    $podNotes = $noteParts ? implode(' | ', $noteParts) : null;
                }

                $status5 = trim((string) ($row[15] ?? '')) ?: null;
                if (! $status5 && ($podReceiver || $podNotes)) {
                    $status5 = $domain->shortWibTimestamp().' - '.($podReceiver ? "Diterima oleh {$podReceiver}" : $podNotes);
                }

                $payload = [
                    'cn_number' => $cn,
                    'shipment_date' => trim((string) ($row[1] ?? '')) ?: $domain->localIsoDate(),
                    'customer_id' => $customer?->id,
                    'customer_name' => $customer?->company_name ?? ($customerName ?: 'Customer Import'),
                    'shipper_address' => $shipperAddress ?: null,
                    'receiver_address' => trim((string) ($row[4] ?? '')) ?: null,
                    'colly' => (int) ($row[5] ?? 0),
                    'weight_kg' => (float) ($row[6] ?? 0),
                    'volume_m3' => (float) ($row[7] ?? 0),
                    'area_m2' => (float) ($row[8] ?? 0),
                    'fleet_id' => $fleetId,
                    'fleet_name' => $fleetName ?: null,
                    'pickup_time' => trim((string) ($row[10] ?? '')) ?: null,
                    'status_1_at' => trim((string) ($row[11] ?? '')) ?: null,
                    'status_2_at' => trim((string) ($row[12] ?? '')) ?: null,
                    'status_3_at' => trim((string) ($row[13] ?? '')) ?: null,
                    'status_4_at' => trim((string) ($row[14] ?? '')) ?: null,
                    'status_5_at' => $status5,
                    'pod_receiver_name' => $podReceiver,
                    'pod_received_at' => $podReceivedAt,
                    'pod_notes' => $podNotes,
                    'service_cost' => (float) preg_replace('/[^0-9.\-]/', '', (string) ($row[17] ?? '0')),
                    'payment_status' => trim((string) ($row[18] ?? '')) ?: 'Belum Lunas',
                    'notes' => trim((string) ($row[19] ?? '')) ?: null,
                ];
                $payload['current_stage'] = $domain->computeCurrentStage($payload);

                $existing = Shipment::query()->where('cn_number', $cn)->first();
                if ($existing) {
                    $existing->update($payload);
                    TrackingLog::create([
                        'shipment_id' => $existing->id,
                        'stage_number' => $existing->current_stage ?: null,
                        'stage_label' => 'Update via Import CSV',
                        'description' => "Data CN {$cn} diperbarui lewat import CSV",
                        'updated_by' => $updatedBy,
                        'created_at' => now('Asia/Jakarta'),
                    ]);
                    $updated++;
                } else {
                    $created = Shipment::create($payload);
                    TrackingLog::create([
                        'shipment_id' => $created->id,
                        'stage_number' => $created->current_stage ?: null,
                        'stage_label' => 'Import CSV Baru',
                        'description' => "Pengiriman {$cn} ditambahkan lewat import CSV",
                        'updated_by' => $updatedBy,
                        'created_at' => now('Asia/Jakarta'),
                    ]);
                    $inserted++;
                }
            }
        });

        return [
            'inserted' => $inserted,
            'updated' => $updated,
            'skipped' => $skipped,
        ];
    }

    private function toCsvRow(array $fields): string
    {
        return implode(',', array_map(function ($val) {
            $str = (string) $val;
            if (str_contains($str, ',') || str_contains($str, '"') || str_contains($str, "\n") || str_contains($str, "\r")) {
                return '"'.str_replace('"', '""', $str).'"';
            }

            return $str;
        }, $fields));
    }
}
