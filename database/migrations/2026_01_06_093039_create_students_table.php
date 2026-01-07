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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('nisn', 20)->unique();
            $table->string('name', 150);
            $table->string('nickname', 50)->nullable();
            $table->enum('gender', ['Laki-laki', 'Perempuan']);
            $table->date('date_of_birth');
            $table->string('place_of_birth', 100)->nullable();
            $table->text('address')->nullable();
            $table->string('photo_url')->nullable();
            $table->foreignId('classroom_id')->nullable()->constrained()->nullOnDelete();
            $table->date('enrollment_date');
            $table->enum('status', ['Aktif', 'Lulus', 'Pindah', 'Keluar'])->default('Aktif');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['classroom_id', 'status']);
            $table->index('nisn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
