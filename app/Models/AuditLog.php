<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AuditLog extends Model {
 public $timestamps=false;
 protected $fillable=['user_id','event','route_name','method','path','response_status','ip_address','user_agent','context','created_at'];
 protected $casts=['context'=>'array','created_at'=>'datetime'];
 public function user(){return $this->belongsTo(User::class);}
}
