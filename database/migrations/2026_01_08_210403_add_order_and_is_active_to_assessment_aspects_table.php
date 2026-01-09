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
        Schema::table('assessment_aspects', function (Blueprint $table) {
            $table->integer('order')->default(0)->comment('Display order')->after('description');
            $table->boolean('is_active')->default(true)->after('order');
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessment_aspects', function (Blueprint $table) {
            $table->dropIndex(['order']);
            $table->dropColumn(['order', 'is_active']);
        });
    }
};
