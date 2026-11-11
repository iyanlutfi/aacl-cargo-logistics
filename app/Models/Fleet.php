<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fleet extends Model
{
    protected $fillable = [
        'fleet_code',
        'plate_number',
        'vehicle_type',
        'driver_name',
        'driver_phone',
        'status',
    ];

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }
}
