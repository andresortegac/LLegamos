<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PayoutAccount extends Model {protected $fillable=['driver_id','holder_name','holder_document','bank_name','account_type','account_number','account_last_four','is_verified'];protected $hidden=['account_number','holder_document'];protected $casts=['account_number'=>'encrypted','holder_document'=>'encrypted','is_verified'=>'boolean'];}