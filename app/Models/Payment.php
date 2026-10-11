<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'trip_id',
        'user_id',
        'amount',
        'currency',
        'amount_in_cents',
        'status',
        'provider',
        'provider_reference',
        'provider_transaction_id',
        'payment_method',
        'paid_at',
        'failed_at',
        'stripe_payment_intent_id',
        'description',
        'notes',
    ];

    protected $casts = [
        'amount' => 'float',
        'amount_in_cents' => 'integer',
        'paid_at' => 'datetime',
        'failed_at' => 'datetime',
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
    public function refunds(){ return $this->hasMany(PaymentRefund::class); }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
