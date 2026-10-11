<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->unsignedInteger('estimated_duration_minutes')->nullable()->after('distance_km');
            $table->decimal('base_fare', 12, 2)->nullable()->after('estimated_cost');
            $table->decimal('distance_fare', 12, 2)->nullable()->after('base_fare');
            $table->decimal('time_fare', 12, 2)->nullable()->after('distance_fare');
            $table->decimal('booking_fee', 12, 2)->nullable()->after('time_fare');
            $table->string('currency', 3)->default('COP')->after('booking_fee');
            $table->string('pricing_version')->nullable()->after('currency');
            $table->string('route_source')->nullable()->after('pricing_version');
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn([
                'estimated_duration_minutes', 'base_fare', 'distance_fare',
                'time_fare', 'booking_fee', 'currency', 'pricing_version', 'route_source',
            ]);
        });
    }
};