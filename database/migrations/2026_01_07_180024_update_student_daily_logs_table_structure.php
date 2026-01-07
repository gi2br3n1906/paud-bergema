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
        // Drop foreign keys and indexes if they exist - using raw SQL
        try {
            DB::statement('ALTER TABLE student_daily_logs DROP FOREIGN KEY student_daily_logs_classroom_id_foreign');
        } catch (\Exception $e) {
            // Already dropped
        }

        try {
            DB::statement('ALTER TABLE student_daily_logs DROP INDEX unique_daily_log');
        } catch (\Exception $e) {
            // Already dropped
        }

        try {
            DB::statement('ALTER TABLE student_daily_logs DROP INDEX student_daily_logs_log_date_classroom_id_index');
        } catch (\Exception $e) {
            // Already dropped
        }

        // Drop old columns if they exist
        try {
            DB::statement('ALTER TABLE student_daily_logs DROP COLUMN classroom_id');
        } catch (\Exception $e) {
            // Already dropped
        }

        try {
            DB::statement('ALTER TABLE student_daily_logs DROP COLUMN prayer_quality');
        } catch (\Exception $e) {
            // Already dropped
        }

        try {
            DB::statement('ALTER TABLE student_daily_logs DROP COLUMN quran_surah');
        } catch (\Exception $e) {
            // Already dropped
        }

        try {
            DB::statement('ALTER TABLE student_daily_logs DROP COLUMN quran_verses');
        } catch (\Exception $e) {
            // Already dropped
        }

        // Rename column if needed
        if (Schema::hasColumn('student_daily_logs', 'log_date')) {
            Schema::table('student_daily_logs', function (Blueprint $table) {
                $table->renameColumn('log_date', 'date');
            });
        }

        // Add new columns if they don't exist
        if (!Schema::hasColumn('student_daily_logs', 'arrival_time')) {
            Schema::table('student_daily_logs', function (Blueprint $table) {
                $table->time('arrival_time')->nullable()->after('attendance_status');
                $table->time('pickup_time')->nullable()->after('arrival_time');
                $table->enum('mood', ['Senang', 'Biasa', 'Sedih', 'Rewel'])->nullable()->after('pickup_time');
                $table->text('activities')->nullable()->after('mood')->comment('Daily activities description');
                $table->text('meals')->nullable()->after('activities')->comment('Meals and eating notes');
                $table->text('nap_notes')->nullable()->after('meals')->comment('Nap/rest time notes');
                $table->text('health_notes')->nullable()->after('nap_notes')->comment('Health observations');
            });
        }

        // Add new indexes if they don't exist
        $indexes = Schema::getIndexes('student_daily_logs');
        $indexNames = array_column($indexes, 'name');

        Schema::table('student_daily_logs', function (Blueprint $table) use ($indexNames) {
            if (!in_array('student_daily_logs_student_id_date_unique', $indexNames)) {
                $table->unique(['student_id', 'date']);
            }
            if (!in_array('student_daily_logs_student_id_date_index', $indexNames)) {
                $table->index(['student_id', 'date']);
            }
            if (!in_array('student_daily_logs_date_index', $indexNames)) {
                $table->index('date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_daily_logs', function (Blueprint $table) {
            $table->renameColumn('date', 'log_date');

            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->enum('prayer_quality', ['Baik', 'Cukup', 'Perlu Bimbingan'])->nullable();
            $table->string('quran_surah', 100)->nullable();
            $table->string('quran_verses', 50)->nullable();

            $table->dropColumn(['arrival_time', 'pickup_time', 'mood', 'activities', 'meals', 'nap_notes', 'health_notes']);

            $table->dropUnique(['student_id', 'date']);
            $table->unique(['student_id', 'classroom_id', 'log_date'], 'unique_daily_log');
            $table->index(['log_date', 'classroom_id']);
            $table->dropIndex(['student_id', 'date']);
            $table->dropIndex(['date']);
        });
    }
};
