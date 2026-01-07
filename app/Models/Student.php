<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nisn',
        'name',
        'nickname',
        'date_of_birth',
        'place_of_birth',
        'gender',
        'address',
        'photo_url',
        'classroom_id',
        'enrollment_date',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'enrollment_date' => 'date',
        ];
    }

    // Relationships

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parent_student')
            ->withPivot('relationship_type', 'is_primary_contact')
            ->withTimestamps();
    }

    public function dailyLogs(): HasMany
    {
        return $this->hasMany(StudentDailyLog::class);
    }

    public function growthRecords(): HasMany
    {
        return $this->hasMany(GrowthRecord::class);
    }

    public function reportCards(): HasMany
    {
        return $this->hasMany(ReportCard::class);
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('status', 'Aktif');
    }

    public function scopeInClassroom($query, int $classroomId)
    {
        return $query->where('classroom_id', $classroomId);
    }

    // Helper methods

    public function getAge(): int
    {
        return $this->date_of_birth->age;
    }

    public function getFullName(): string
    {
        return $this->nickname ? "{$this->name} ({$this->nickname})" : $this->name;
    }
}
