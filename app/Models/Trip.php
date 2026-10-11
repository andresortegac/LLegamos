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
        'pickup_pin',
        'estimated_cost',
        'estimated_duration_minutes',
        'base_fare',
        'distance_fare',
        'time_fare',
        'booking_fee',
        'currency',
        'pricing_version',
        'route_source',
        'final_cost',
        'start_time',
        'end_time',
        'distance_km',
        'driver_distance_km',
        'cancelled_by',
        'cancellation_reason',
        'cancelled_at',
        'notes',
    ];

    protected $hidden = ['pickup_pin'];

    protected function casts(): array
    {
        return [
            'pickup_pin' => 'encrypted',
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'cancelled_at' => 'datetime',
            'estimated_cost' => 'decimal:2',
            'final_cost' => 'decimal:2',
            'base_fare' => 'decimal:2',
            'distance_fare' => 'decimal:2',
            'time_fare' => 'decimal:2',
            'booking_fee' => 'decimal:2',
        ];
    }

    public function passenger()
    {
        return $this->belongsTo(User::class, 'passenger_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function offers(){ return $this->hasMany(TripOffer::class); }

    public function ratings(){ return $this->hasMany(TripRating::class); }

    public function incidents(){ return $this->hasMany(TripIncident::class); }

    public function earning(){ return $this->hasOne(DriverEarning::class); }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
} 
