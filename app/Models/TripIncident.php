<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TripIncident extends Model { protected $fillable=['trip_id','reported_by','type','severity','status','description','latitude','longitude','assigned_admin_id','resolution_notes','resolved_at']; protected $casts=['resolved_at'=>'datetime']; public function trip(){return $this->belongsTo(Trip::class);} public function reporter(){return $this->belongsTo(User::class,'reported_by');} }