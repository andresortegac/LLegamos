<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('id_document_front_path')->nullable()->after('role');
            $table->string('id_document_back_path')->nullable()->after('id_document_front_path');
            $table->string('face_biometric_path')->nullable()->after('id_document_back_path');
            $table->boolean('is_identity_verified')->default(false)->after('face_biometric_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'id_document_front_path',
                'id_document_back_path',
                'face_biometric_path',
                'is_identity_verified',
            ]);
        });
    }
};
