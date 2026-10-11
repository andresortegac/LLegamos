<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('emergency_contacts', function(Blueprint $table){$table->id();$table->foreignId('user_id')->constrained()->cascadeOnDelete();$table->string('name');$table->string('phone',30);$table->string('relationship',80)->nullable();$table->timestamps();});
  Schema::create('trip_share_tokens', function(Blueprint $table){$table->id();$table->foreignId('trip_id')->constrained()->cascadeOnDelete();$table->foreignId('created_by')->constrained('users')->cascadeOnDelete();$table->string('token_hash',64)->unique();$table->timestamp('expires_at')->index();$table->timestamp('revoked_at')->nullable();$table->timestamps();});
  Schema::create('trip_incidents', function(Blueprint $table){$table->id();$table->foreignId('trip_id')->constrained()->cascadeOnDelete();$table->foreignId('reported_by')->constrained('users')->cascadeOnDelete();$table->enum('type',['sos','safety','conduct','vehicle','lost_item','other'])->index();$table->enum('severity',['low','medium','high','critical'])->index();$table->enum('status',['open','in_review','resolved','dismissed'])->default('open')->index();$table->text('description');$table->decimal('latitude',10,8)->nullable();$table->decimal('longitude',11,8)->nullable();$table->foreignId('assigned_admin_id')->nullable()->constrained('users')->nullOnDelete();$table->text('resolution_notes')->nullable();$table->timestamp('resolved_at')->nullable();$table->timestamps();});
 }
 public function down(): void {Schema::dropIfExists('trip_incidents');Schema::dropIfExists('trip_share_tokens');Schema::dropIfExists('emergency_contacts');}
};