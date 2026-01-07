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
        Schema::create('assessment_aspects', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('category', 100)->comment('e.g. Nilai Agama dan Moral, Kognitif, Fisik Motorik');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_aspects');
    }
};
