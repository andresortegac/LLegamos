<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    protected $fillable = [
        'passenger_id',
        'driver_id',
        'vehicle_type',
        'department',
        'municipality',
        'origin',
        'destination',
        'origin_lat',
        'origin_lng',
        'destination_lat',
        'destination_lng',
        'status',
        'estimated_cost',
        'final_cost',
        'start_time',
        'end_time',
        'distance_km',
        'notes',
    ];

    public function passenger()
    {
        return $this->belongsTo(User::class, 'passenger_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
} 
