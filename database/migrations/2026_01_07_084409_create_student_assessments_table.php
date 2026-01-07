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
        Schema::create('student_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_term_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_aspect_id')->constrained()->cascadeOnDelete();
            $table->enum('score', ['BB', 'MB', 'BSH', 'BSB'])->comment('BB=Belum Berkembang, MB=Mulai Berkembang, BSH=Berkembang Sesuai Harapan, BSB=Berkembang Sangat Baik');
            $table->text('notes')->nullable();
            $table->foreignId('assessed_by')->constrained('users')->comment('Teacher who assessed');
            $table->timestamps();

            $table->unique(['student_id', 'academic_term_id', 'assessment_aspect_id'], 'unique_student_assessment');
            $table->index(['classroom_id', 'academic_term_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_assessments');
    }
};
