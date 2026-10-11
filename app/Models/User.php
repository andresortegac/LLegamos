<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmailContract
{
    use HasApiTokens, HasFactory, Notifiable, MustVerifyEmail;

    protected $attributes = ['status' => 'active'];

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'role',
        'id_document_front_path',
        'id_document_back_path',
        'face_biometric_path',
        'is_identity_verified',
        'status',
        'suspended_at',
        'suspension_reason',
        'suspended_by',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'suspended_at' => 'datetime', 'last_login_at' => 'datetime'];
    }

    public function driverProfile()
    {
        return $this->hasOne(DriverProfile::class);
    }

    public function sentMessages()
    {
        return $this->hasMany(InternalMessage::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(InternalMessage::class, 'recipient_id');
    }
}
