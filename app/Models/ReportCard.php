<?php

namespace App\Models;

use App\Enums\ReportCardStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportCard extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'academic_term_id',
        'classroom_id',
        'status',
        'published_at',
        'created_by',
        'reviewed_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReportCardStatus::class,
            'published_at' => 'datetime',
        ];
    }

    // Relationships

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicTerm(): BelongsTo
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(ReportDetail::class);
    }

    // Scopes

    public function scopeDraft($query)
    {
        return $query->where('status', ReportCardStatus::DRAFT);
    }

    public function scopePublished($query)
    {
        return $query->where('status', ReportCardStatus::PUBLISHED);
    }

    // Helper methods

    public function publish(): void
    {
        $this->update([
            'status' => ReportCardStatus::PUBLISHED,
            'published_at' => now(),
        ]);
    }

    public function isDraft(): bool
    {
        return $this->status === ReportCardStatus::DRAFT;
    }

    public function isPublished(): bool
    {
        return $this->status === ReportCardStatus::PUBLISHED;
    }
}
