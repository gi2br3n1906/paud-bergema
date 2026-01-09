<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the old report_details table and recreate with new structure
        Schema::dropIfExists('report_details');

        Schema::create('report_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_aspect_id')->constrained()->cascadeOnDelete();

            // Assessment Scale: BB, MB, BSH, BSB
            $table->enum('score', ['BB', 'MB', 'BSH', 'BSB']);

            // Keywords for AI narrative generation (comma-separated or JSON)
            $table->text('keywords')->nullable()->comment('Keywords untuk generate narasi dengan AI');

            // Optional: Manual narrative override
            $table->text('narrative')->nullable()->comment('Narasi deskripsi perkembangan');

            $table->timestamps();

            // Unique constraint: one assessment per aspect per report
            $table->unique(['report_card_id', 'assessment_aspect_id'], 'unique_report_aspect');

            // Indexes for performance
            $table->index('report_card_id');
            $table->index('score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration cannot be fully reversed as we're dropping data
        // Recreate the old structure if needed
        Schema::dropIfExists('report_details');

        Schema::create('report_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_aspect_id')->constrained()->cascadeOnDelete();
            $table->text('keywords');
            $table->enum('predicate', ['baik', 'cukup', 'perlu_bimbingan']);
            $table->text('ai_generated_narrative')->nullable();
            $table->text('final_narrative');
            $table->enum('generation_status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->timestamps();
        });
    }
};
