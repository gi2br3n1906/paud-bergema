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
        Schema::create('class_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->date('date');
            $table->string('theme')->nullable()->comment('Daily theme/topic');
            $table->text('activity_summary');
            $table->json('photos')->nullable()->comment('Array of photo URLs');
            $table->json('attendance_stats')->nullable()->comment('JSON: {present: X, sick: Y, permission: Z, absent: W}');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['classroom_id', 'date']);
            $table->index('date');

            // Prevent duplicate entries for same classroom on same date
            $table->unique(['classroom_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_journals');
    }
};
