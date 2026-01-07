<?php

namespace App\Models;

use App\Enums\GrowthStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowthRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'measurement_date',
        'height_cm',
        'weight_kg',
        'head_circumference_cm',
        'z_score_height',
        'z_score_weight',
        'growth_status',
        'notes',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'measurement_date' => 'date',
            'height_cm' => 'decimal:2',
            'weight_kg' => 'decimal:2',
            'head_circumference_cm' => 'decimal:2',
            'z_score_height' => 'decimal:2',
            'z_score_weight' => 'decimal:2',
            'growth_status' => GrowthStatus::class,
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

    // Scopes

    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('measurement_date', 'desc');
    }

    // Helper methods

    public function isHealthy(): bool
    {
        return $this->growth_status?->isHealthy() ?? false;
    }
}
