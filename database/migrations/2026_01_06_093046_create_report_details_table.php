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
        Schema::create('report_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_aspect_id')->constrained();
            $table->text('keywords')->comment('Teacher input: e.g. mandiri, aktif, suka berbagi');
            $table->enum('predicate', ['baik', 'cukup', 'perlu_bimbingan']);
            $table->text('ai_generated_narrative')->nullable()->comment('Gemini output');
            $table->text('final_narrative')->comment('Teacher-edited version');
            $table->enum('generation_status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->timestamps();

            $table->index(['report_card_id', 'assessment_aspect_id'], 'idx_report_aspect');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_details');
    }
};
