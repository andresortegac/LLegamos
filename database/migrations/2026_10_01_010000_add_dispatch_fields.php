<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('driver_profiles', function (Blueprint $table) {
            $table->boolean('is_online')->default(false)->index()->after('status');
            $table->decimal('current_lat', 10, 8)->nullable()->after('is_online');
            $table->decimal('current_lng', 11, 8)->nullable()->after('current_lat');
            $table->timestamp('location_updated_at')->nullable()->after('current_lng');
        });

        Schema::table('trips', function (Blueprint $table) {
            $table->text('pickup_pin')->nullable()->after('status');
            $table->string('cancelled_by')->nullable()->after('end_time');
            $table->string('cancellation_reason', 500)->nullable()->after('cancelled_by');
            $table->timestamp('cancelled_at')->nullable()->after('cancellation_reason');
            $table->decimal('driver_distance_km', 10, 2)->nullable()->after('distance_km');
        });

        DB::table('trips')
            ->whereIn('status', ['pending', 'accepted'])
            ->whereNull('pickup_pin')
            ->orderBy('id')
            ->eachById(function ($trip): void {
                DB::table('trips')->where('id', $trip->id)->update([
                    'pickup_pin' => Crypt::encryptString((string) random_int(1000, 9999)),
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn(['pickup_pin', 'cancelled_by', 'cancellation_reason', 'cancelled_at', 'driver_distance_km']);
        });

        Schema::table('driver_profiles', function (Blueprint $table) {
            $table->dropColumn(['is_online', 'current_lat', 'current_lng', 'location_updated_at']);
        });
    }
};