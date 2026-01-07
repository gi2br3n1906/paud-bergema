<?php

namespace App\Models;

use App\Enums\DailyLogType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentDailyLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'log_date',
        'log_type',
        'data',
        'recorded_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'log_date' => 'date',
            'log_type' => DailyLogType::class,
        ];
    }

    // Relationships

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    // Accessors & Mutators with dynamic casts

    protected function data(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: function (mixed $value) {
                if ($value === null) {
                    return null;
                }

                $decoded = is_string($value) ? json_decode($value, true) : $value;

                return match($this->log_type) {
                    DailyLogType::PRESENCE => \App\DataTransferObjects\DailyLog\PresenceData::from($decoded),
                    DailyLogType::WORSHIP => \App\DataTransferObjects\DailyLog\WorshipData::from($decoded),
                    DailyLogType::QURAN => \App\DataTransferObjects\DailyLog\QuranProgressData::from($decoded),
                    default => $decoded,
                };
            },
            set: function (mixed $value) {
                if ($value === null) {
                    return null;
                }

                // If it's a DTO, convert to array
                if (method_exists($value, 'toArray')) {
                    return json_encode($value->toArray());
                }

                // If it's already an array
                if (is_array($value)) {
                    return json_encode($value);
                }

                return $value;
            }
        );
    }

    // Scopes

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('log_date', $date);
    }

    public function scopeOfType($query, DailyLogType $type)
    {
        return $query->where('log_type', $type);
    }

    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }
}
