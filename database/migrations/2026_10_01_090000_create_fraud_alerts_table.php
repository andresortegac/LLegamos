<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('fraud_alerts',function(Blueprint $table){
   $table->id();
   $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
   $table->foreignId('trip_id')->nullable()->constrained()->cascadeOnDelete();
   $table->string('rule');
   $table->string('severity',20)->default('medium')->index();
   $table->string('status',20)->default('open')->index();
   $table->string('fingerprint')->unique();
   $table->text('summary');
   $table->json('details')->nullable();
   $table->timestamp('detected_at')->useCurrent();
   $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
   $table->text('review_notes')->nullable();
   $table->timestamp('reviewed_at')->nullable();
   $table->timestamps();
   $table->index(['user_id','detected_at']);
  });
 }
 public function down(): void {Schema::dropIfExists('fraud_alerts');}
};
