<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shipment extends Model
{
    protected $fillable = [
        'cn_number',
        'shipment_date',
        'customer_id',
        'customer_name',
        'shipper_address',
        'receiver_address',
        'colly',
        'weight_kg',
        'volume_m3',
        'area_m2',
        'fleet_id',
        'fleet_name',
        'pickup_time',
        'service_cost',
        'payment_status',
        'status_1_at',
        'status_2_at',
        'status_3_at',
        'status_4_at',
        'status_5_at',
        'pod_receiver_name',
        'pod_received_at',
        'pod_photo_url',
        'pod_signature_data',
        'pod_notes',
        'current_stage',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'colly' => 'integer',
            'weight_kg' => 'float',
            'volume_m3' => 'float',
            'area_m2' => 'float',
            'service_cost' => 'float',
            'current_stage' => 'integer',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function fleet(): BelongsTo
    {
        return $this->belongsTo(Fleet::class);
    }

    public function trackingLogs(): HasMany
    {
        return $this->hasMany(TrackingLog::class)->orderByDesc('id');
    }

    public function hasPod(): bool
    {
        return filled($this->pod_receiver_name)
            || filled($this->pod_photo_url)
            || filled($this->pod_signature_data);
    }
}
