<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DriverEarning extends Model
{
    protected $fillable = ['trip_id','driver_id','payout_request_id','gross_amount','commission_rate','platform_fee','net_amount','status','available_at','paid_at'];
    protected $casts = ['gross_amount'=>'decimal:2','commission_rate'=>'decimal:2','platform_fee'=>'decimal:2','net_amount'=>'decimal:2','available_at'=>'datetime','paid_at'=>'datetime'];
    public function trip(){ return $this->belongsTo(Trip::class); }
    public function driver(){ return $this->belongsTo(User::class, 'driver_id'); }
}