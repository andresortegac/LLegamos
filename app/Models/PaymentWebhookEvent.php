<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;
class PaymentWebhookEvent extends Model{protected $fillable=['provider','checksum','event_type','external_id','payload','processed_at'];protected function casts():array{return ['payload'=>'array','processed_at'=>'datetime'];}}