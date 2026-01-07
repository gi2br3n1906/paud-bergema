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
        Schema::table('users', function (Blueprint $table) {
            // Make email nullable (parents don't need email)
            $table->string('email')->nullable()->change();

            // Make phone_number unique and NOT nullable (primary login credential for parents)
            // Increased to 25 chars to accommodate international format (+62xxx)
            $table->string('phone_number', 25)->nullable(false)->unique()->change();

            // Add index for faster authentication lookups
            $table->index('phone_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert email back to NOT nullable
            $table->string('email')->nullable(false)->change();

            // Revert phone_number back to nullable and remove unique constraint
            $table->dropUnique(['phone_number']);
            $table->dropIndex(['phone_number']);
            $table->string('phone_number', 25)->nullable()->change();
        });
    }
};
