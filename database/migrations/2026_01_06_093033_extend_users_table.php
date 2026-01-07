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
            $table->enum('role', ['admin', 'teacher', 'parent'])->default('parent')->after('email');
            $table->string('phone_number', 20)->nullable()->after('role');
            $table->text('address')->nullable()->after('phone_number');
            $table->string('photo_url')->nullable()->after('address');
            $table->string('teacher_competency')->nullable()->after('photo_url')->comment('For teacher directory');
            $table->text('teacher_motto')->nullable()->after('teacher_competency');
            $table->boolean('is_active')->default(true)->after('teacher_motto');

            $table->index('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropColumn([
                'role',
                'phone_number',
                'address',
                'photo_url',
                'teacher_competency',
                'teacher_motto',
                'is_active',
            ]);
        });
    }
};
