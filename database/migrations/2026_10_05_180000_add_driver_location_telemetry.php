<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('driver_profiles', function (Blueprint $table) {
            $table->decimal('location_accuracy_m', 8, 2)->nullable()->after('location_updated_at');
            $table->decimal('location_heading', 6, 2)->nullable()->after('location_accuracy_m');
            $table->decimal('location_speed_mps', 8, 2)->nullable()->after('location_heading');
        });
    }

    public function down(): void
    {
        Schema::table('driver_profiles', function (Blueprint $table) {
            $table->dropColumn(['location_accuracy_m', 'location_heading', 'location_speed_mps']);
        });
    }
};