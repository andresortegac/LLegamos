<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PayoutRequest extends Model {protected $fillable=['driver_id','payout_account_id','amount','status','reviewed_by','payment_reference','review_notes','reviewed_at','paid_at'];protected $casts=['amount'=>'decimal:2','reviewed_at'=>'datetime','paid_at'=>'datetime'];public function driver(){return $this->belongsTo(User::class,'driver_id');}public function account(){return $this->belongsTo(PayoutAccount::class,'payout_account_id');}public function earnings(){return $this->hasMany(DriverEarning::class);}}