<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('driver_profiles', function (Blueprint $table) {
            $table->date('birth_date')->nullable()->after('vehicle_type');
            $table->integer('vehicle_model_year')->nullable()->after('birth_date');
            $table->enum('plate_type', ['particular', 'publico'])->nullable()->after('vehicle_model_year');
            $table->boolean('has_four_doors')->nullable()->after('plate_type');
            $table->boolean('has_seatbelts')->nullable()->after('has_four_doors');
            $table->boolean('has_air_conditioning')->nullable()->after('has_seatbelts');
            $table->boolean('background_check_passed')->default(false)->after('has_air_conditioning');

            $table->string('profile_photo_path')->nullable()->after('background_check_passed');
            $table->string('license_document_path')->nullable()->after('profile_photo_path');
            $table->string('property_card_path')->nullable()->after('license_document_path');
            $table->string('soat_document_path')->nullable()->after('property_card_path');

            $table->enum('verification_status', ['pending', 'approved', 'rejected'])->default('pending')->after('soat_document_path');
            $table->text('verification_notes')->nullable()->after('verification_status');
            $table->timestamp('submitted_at')->nullable()->after('verification_notes');
            $table->timestamp('verified_at')->nullable()->after('submitted_at');
            $table->foreignId('verified_by_admin_id')->nullable()->after('verified_at')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('driver_profiles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('verified_by_admin_id');
            $table->dropColumn([
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
                'verification_status',
                'verification_notes',
                'submitted_at',
                'verified_at',
            ]);
        });
    }
};
