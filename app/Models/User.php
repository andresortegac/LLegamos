<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'id_document_front_path',
        'id_document_back_path',
        'face_biometric_path',
        'is_identity_verified',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

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
