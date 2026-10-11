<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;
class LivenessChallenge extends Model{protected $fillable=['user_id','token_hash','instruction','expires_at','consumed_at'];protected function casts():array{return ['expires_at'=>'datetime','consumed_at'=>'datetime'];}public function user(){return $this->belongsTo(User::class);}}