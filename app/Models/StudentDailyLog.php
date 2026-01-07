<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentDailyLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'recorded_by',
        'date',
        'attendance_status',
        'arrival_time',
        'pickup_time',
        'mood',
        'activities',
        'meals',
        'nap_notes',
        'health_notes',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'arrival_time' => 'datetime:H:i',
            'pickup_time' => 'datetime:H:i',
        ];
    }

    // Relationships

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    // Scopes

    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
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

    public function isPresent(): bool
    {
        return $this->attendance_status === 'Hadir';
    }

    public function getMoodEmoji(): string
    {
        return match ($this->mood) {
            'Senang' => '😊',
            'Biasa' => '😐',
            'Sedih' => '😢',
            'Rewel' => '😠',
            default => '😐',
        };
    }
}
