<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::table('users',function(Blueprint $table){$table->timestamp('email_verified_at')->nullable()->after('email');});
  DB::table('users')->whereNull('email_verified_at')->update(['email_verified_at'=>now()]);
  if(!Schema::hasTable('password_reset_tokens')){Schema::create('password_reset_tokens',function(Blueprint $table){$table->string('email')->primary();$table->string('token');$table->timestamp('created_at')->nullable();});}
  Schema::create('audit_logs',function(Blueprint $table){$table->id();$table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();$table->string('event');$table->string('route_name')->nullable();$table->string('method',10);$table->string('path');$table->unsignedSmallInteger('response_status');$table->string('ip_address',45)->nullable();$table->string('user_agent',500)->nullable();$table->json('context')->nullable();$table->timestamp('created_at')->useCurrent();$table->index(['user_id','created_at']);$table->index(['event','created_at']);});
 }
 public function down(): void {Schema::dropIfExists('audit_logs');Schema::dropIfExists('password_reset_tokens');Schema::table('users',fn(Blueprint $table)=>$table->dropColumn('email_verified_at'));}
};