<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackingLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'shipment_id',
        'stage_number',
        'stage_label',
        'description',
        'updated_by',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'stage_number' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }
}
