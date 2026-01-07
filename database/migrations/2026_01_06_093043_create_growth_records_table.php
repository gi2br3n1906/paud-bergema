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
        Schema::create('growth_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->date('measurement_date');
            $table->decimal('height_cm', 5, 2);
            $table->decimal('weight_kg', 5, 2);
            $table->decimal('head_circumference_cm', 5, 2)->nullable();
            $table->decimal('z_score_height', 4, 2)->nullable()->comment('WHO 2006 Z-Score');
            $table->decimal('z_score_weight', 4, 2)->nullable()->comment('WHO 2006 Z-Score');
            $table->enum('growth_status', ['normal', 'stunting', 'overweight', 'underweight'])->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();

            $table->index(['student_id', 'measurement_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('growth_records');
    }
};
