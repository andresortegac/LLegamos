<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('trip_offers',function(Blueprint $table){$table->id();$table->foreignId('trip_id')->constrained()->cascadeOnDelete();$table->foreignId('driver_id')->constrained('users')->cascadeOnDelete();$table->decimal('distance_km',10,2);$table->enum('status',['pending','accepted','rejected','expired'])->default('pending');$table->timestamp('expires_at');$table->timestamp('responded_at')->nullable();$table->timestamps();$table->unique(['trip_id','driver_id']);$table->index(['driver_id','status','expires_at']);$table->index(['trip_id','status']);}); }
 public function down(): void { Schema::dropIfExists('trip_offers'); }
};
