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
        Schema::create('student_daily_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->date('log_date');

            // Attendance
            $table->enum('attendance_status', ['Hadir', 'Sakit', 'Izin', 'Alpa'])->default('Hadir');

            // Prayer/Worship quality
            $table->enum('prayer_quality', ['Baik', 'Cukup', 'Perlu Bimbingan'])->nullable();

            // Quran learning
            $table->string('quran_surah', 100)->nullable();
            $table->string('quran_verses', 50)->nullable();

            $table->foreignId('recorded_by')->constrained('users')->comment('Teacher who recorded');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'classroom_id', 'log_date'], 'unique_daily_log');
            $table->index(['log_date', 'classroom_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_daily_logs');
    }
};
