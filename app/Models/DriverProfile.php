<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverProfile extends Model
{
    protected $fillable = [
        'user_id',
        'document_number',
        'license_number',
        'vehicle_plate',
        'vehicle_type',
        'birth_date',
        'vehicle_model_year',
        'plate_type',
        'has_four_doors',
        'has_seatbelts',
        'has_air_conditioning',
        'background_check_passed',
        'profile_photo_path',
        'license_document_path',
        'property_card_path',
        'soat_document_path',
        'id_card_document_path',
        'verification_status',
        'verification_notes',
        'submitted_at',
        'verified_at',
        'verified_by_admin_id',
        'status',
        'rating',
        'total_trips',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'has_four_doors' => 'boolean',
        'has_seatbelts' => 'boolean',
        'has_air_conditioning' => 'boolean',
        'background_check_passed' => 'boolean',
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function verifiedByAdmin()
    {
        return $this->belongsTo(User::class, 'verified_by_admin_id');
    }
}
