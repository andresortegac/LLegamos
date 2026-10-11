<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::table('users', function (Blueprint $table) {
   $table->string('status',20)->default('active')->index();
   $table->timestamp('suspended_at')->nullable();
   $table->text('suspension_reason')->nullable();
   $table->foreignId('suspended_by')->nullable()->constrained('users')->nullOnDelete();
   $table->timestamp('last_login_at')->nullable();
  });
  DB::table('users')->whereNull('status')->update(['status'=>'active']);
 }
 public function down(): void {
  Schema::table('users', function (Blueprint $table) {
   $table->dropForeign(['suspended_by']); $table->dropIndex(['status']);
   $table->dropColumn(['status','suspended_at','suspension_reason','suspended_by','last_login_at']);
  });
 }
};
