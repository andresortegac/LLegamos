<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverProfile extends Model
{
    protected $fillable = [
        'user_id',
        'document_number',
        'license_number',
        'license_expires_at',
        'vehicle_plate',
        'vehicle_type',
        'vehicle_make',
        'vehicle_model',
        'vehicle_color',
        'passenger_capacity',
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
        'is_vehicle_owner',
        'owner_authorization_path',
        'soat_document_path',
        'soat_expires_at',
        'technical_review_expires_at',
        'technical_review_path',
        'background_check_consent_at',
        'policies_accepted_at',
        'training_completed_at',
        'verification_checks',
        'id_card_document_path',
        'verification_status',
        'verification_notes',
        'submitted_at',
        'verified_at',
        'verified_by_admin_id',
        'status',
        'is_online',
        'current_lat',
        'current_lng',
        'location_updated_at',
                'location_accuracy_m',
        'location_heading',
        'location_speed_mps',
'liveness_challenge_id',
        'selfie_captured_at',
        'identity_provider',
        'identity_provider_reference',
        'face_match_score',
        'liveness_score',
        'rating',
        'total_trips',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'license_expires_at' => 'date',
        'soat_expires_at' => 'date',
        'technical_review_expires_at' => 'date',
        'is_vehicle_owner' => 'boolean',
        'has_four_doors' => 'boolean',
        'has_seatbelts' => 'boolean',
        'has_air_conditioning' => 'boolean',
        'background_check_passed' => 'boolean',
        'is_online' => 'boolean',
        'current_lat' => 'decimal:8',
        'current_lng' => 'decimal:8',
        'location_updated_at' => 'datetime',
                'location_accuracy_m' => 'decimal:2',
        'location_heading' => 'decimal:2',
        'location_speed_mps' => 'decimal:2',
'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
        'background_check_consent_at' => 'datetime',
        'policies_accepted_at' => 'datetime',
        'training_completed_at' => 'datetime',
        'selfie_captured_at' => 'datetime',
        'verification_checks' => 'array',
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
