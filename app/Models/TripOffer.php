<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TripOffer extends Model
{
 protected $fillable=['trip_id','driver_id','distance_km','status','expires_at','responded_at'];
 protected $casts=['distance_km'=>'decimal:2','expires_at'=>'datetime','responded_at'=>'datetime'];
 public function trip(){return $this->belongsTo(Trip::class);}
 public function driver(){return $this->belongsTo(User::class,'driver_id');}
 public function isActionable(): bool{return $this->status==='pending' && $this->expires_at->isFuture();}
}
