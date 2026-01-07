<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassJournal extends Model
{
    use HasFactory;

    protected $fillable = [
        'classroom_id',
        'teacher_id',
        'date',
        'theme',
        'activity_summary',
        'photos',
        'attendance_stats',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'photos' => 'array',
            'attendance_stats' => 'array',
        ];
    }

    // Relationships

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    // Scopes

    public function scopeForClassroom($query, int $classroomId)
    {
        return $query->where('classroom_id', $classroomId);
    }

    public function scopeForDate($query, string $date)
    {
        return $query->where('date', $date);
    }

    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderBy('date', 'desc')->limit($limit);
    }

    // Helper methods

    public function getTotalAttendance(): int
    {
        $stats = $this->attendance_stats ?? [];
        return ($stats['present'] ?? 0) + ($stats['sick'] ?? 0) +
               ($stats['permission'] ?? 0) + ($stats['absent'] ?? 0);
    }

    public function getAttendancePercentage(): float
    {
        $total = $this->getTotalAttendance();
        if ($total === 0) {
            return 0;
        }
        $present = $this->attendance_stats['present'] ?? 0;
        return round(($present / $total) * 100, 1);
    }
}
