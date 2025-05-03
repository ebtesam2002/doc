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
    {Schema::create('doctor_schedules', function (Blueprint $table) {
        $table->id();
        $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
        $table->string('day'); // مثل: 'sunday', 'monday', ...
        $table->integer('slots'); // عدد الكشوفات
        $table->time('start_time');
        $table->time('end_time');
        $table->decimal('booking_price', 8, 2);
        $table->string('address');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_schedules');
    }
};
