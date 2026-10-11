<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class FraudAlert extends Model {
 protected $fillable=['user_id','trip_id','rule','severity','status','fingerprint','summary','details','detected_at','reviewed_by','review_notes','reviewed_at'];
 protected $casts=['details'=>'array','detected_at'=>'datetime','reviewed_at'=>'datetime'];
 public function user(){return $this->belongsTo(User::class);}
 public function trip(){return $this->belongsTo(Trip::class);}
 public function reviewer(){return $this->belongsTo(User::class,'reviewed_by');}
}
