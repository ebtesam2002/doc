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
        $table->string('specialization')->nullable();
        $table->string('license_number')->unique()->nullable();
        $table->string('verification_code')->nullable();
        $table->boolean('is_verified')->default(false);
        
        // تعديل الـ role لإضافة doctor
        $table->enum('role', ['admin', 'user', 'doctor'])->default('user')->change();
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn([
            'specialization',
            'license_number',
            'verification_code',
            'is_verified',
        ]);

        // الرجوع للنسخة القديمة من enum (بدون doctor)
        $table->enum('role', ['admin', 'user'])->default('user')->change();
    });
}

};
