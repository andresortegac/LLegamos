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
        Schema::create('driver_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('document_number')->unique();
            $table->string('license_number')->unique();
            $table->string('vehicle_plate')->unique();
            $table->string('vehicle_type'); // auto, moto, bicicleta
            $table->string('status')->default('active'); // active, inactive, suspended
            $table->decimal('rating', 2, 1)->default(5.0);
            $table->integer('total_trips')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_profiles');
    }
};
