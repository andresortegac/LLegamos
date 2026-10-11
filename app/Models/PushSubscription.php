<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PushSubscription extends Model { protected $fillable=['user_id','endpoint_hash','endpoint','public_key','auth_token','content_encoding','user_agent','last_used_at']; protected $hidden=['endpoint','public_key','auth_token']; protected function casts(): array{return ['last_used_at'=>'datetime'];} public function user(){return $this->belongsTo(User::class);} }