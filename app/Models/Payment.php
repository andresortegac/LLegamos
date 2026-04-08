<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'trip_id',
        'user_id',
        'amount',
        'status',
        'stripe_payment_intent_id',
        'description',
        'notes',
    ];

    protected $casts = [
        'amount' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the trip associated with this payment
     */
    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    /**
     * Get the user (passenger) who made the payment
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
