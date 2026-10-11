<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::table('driver_profiles', function(Blueprint $table){
  $table->string('vehicle_make')->nullable(); $table->string('vehicle_model')->nullable(); $table->string('vehicle_color')->nullable(); $table->unsignedTinyInteger('passenger_capacity')->nullable();
  $table->date('license_expires_at')->nullable(); $table->date('soat_expires_at')->nullable(); $table->date('technical_review_expires_at')->nullable(); $table->string('technical_review_path')->nullable();
  $table->boolean('is_vehicle_owner')->default(true); $table->string('owner_authorization_path')->nullable();
  $table->timestamp('background_check_consent_at')->nullable(); $table->timestamp('policies_accepted_at')->nullable(); $table->timestamp('training_completed_at')->nullable(); $table->json('verification_checks')->nullable();
 }); }
 public function down(): void { Schema::table('driver_profiles', function(Blueprint $table){ $table->dropColumn(['vehicle_make','vehicle_model','vehicle_color','passenger_capacity','license_expires_at','soat_expires_at','technical_review_expires_at','technical_review_path','is_vehicle_owner','owner_authorization_path','background_check_consent_at','policies_accepted_at','training_completed_at','verification_checks']); }); }
};
