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
        Schema::create('prescriptions', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('doctor_id');
    $table->unsignedBigInteger('user_id');
    $table->unsignedBigInteger('booking_id');
    $table->text('medicines'); // يمكن أن يكون نص يحتوي على الأدوية
    $table->timestamps();

    $table->foreign('doctor_id')->references('id')->on('users')->onDelete('cascade');
    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
