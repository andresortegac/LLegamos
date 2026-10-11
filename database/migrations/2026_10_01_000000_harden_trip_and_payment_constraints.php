<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->decimal('distance_km', 10, 2)->nullable()->change();
            $table->index(['status', 'driver_id']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->unique('trip_id');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique(['trip_id']);
        });

        Schema::table('trips', function (Blueprint $table) {
            $table->dropIndex(['status', 'driver_id']);
            $table->integer('distance_km')->nullable()->change();
        });
    }
};