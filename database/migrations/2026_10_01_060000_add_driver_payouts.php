<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('payout_accounts',function(Blueprint $table){$table->id();$table->foreignId('driver_id')->unique()->constrained('users')->cascadeOnDelete();$table->string('holder_name');$table->string('holder_document');$table->string('bank_name');$table->enum('account_type',['ahorros','corriente','wallet']);$table->text('account_number');$table->string('account_last_four',4);$table->boolean('is_verified')->default(false);$table->timestamps();});
  Schema::create('payout_requests',function(Blueprint $table){$table->id();$table->foreignId('driver_id')->constrained('users')->cascadeOnDelete();$table->foreignId('payout_account_id')->constrained()->restrictOnDelete();$table->decimal('amount',12,2);$table->enum('status',['pending','approved','paid','rejected'])->default('pending')->index();$table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();$table->string('payment_reference')->nullable();$table->text('review_notes')->nullable();$table->timestamp('reviewed_at')->nullable();$table->timestamp('paid_at')->nullable();$table->timestamps();});
  Schema::table('driver_earnings',function(Blueprint $table){$table->foreignId('payout_request_id')->nullable()->after('driver_id')->constrained()->nullOnDelete();});
 }
 public function down(): void {Schema::table('driver_earnings',fn(Blueprint $table)=>$table->dropConstrainedForeignId('payout_request_id'));Schema::dropIfExists('payout_requests');Schema::dropIfExists('payout_accounts');}
};