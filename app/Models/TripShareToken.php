<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TripShareToken extends Model { protected $fillable=['trip_id','created_by','token_hash','expires_at','revoked_at']; protected $casts=['expires_at'=>'datetime','revoked_at'=>'datetime']; public function trip(){return $this->belongsTo(Trip::class);} }