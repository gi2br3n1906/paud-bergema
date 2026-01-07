<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_card_id',
        'assessment_aspect_id',
        'keywords',
        'predicate',
        'ai_generated_narrative',
        'final_narrative',
        'generation_status',
    ];

    // Relationships

    public function reportCard(): BelongsTo
    {
        return $this->belongsTo(ReportCard::class);
    }

    public function assessmentAspect(): BelongsTo
    {
        return $this->belongsTo(AssessmentAspect::class);
    }

    // Scopes

    public function scopePending($query)
    {
        return $query->where('generation_status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('generation_status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('generation_status', 'failed');
    }

    // Helper methods

    public function markAsProcessing(): void
    {
        $this->update(['generation_status' => 'processing']);
    }

    public function markAsCompleted(string $narrative): void
    {
        $this->update([
            'ai_generated_narrative' => $narrative,
            'final_narrative' => $narrative, // Default to AI output
            'generation_status' => 'completed',
        ]);
    }

    public function markAsFailed(): void
    {
        $this->update(['generation_status' => 'failed']);
    }

    public function isPending(): bool
    {
        return $this->generation_status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->generation_status === 'completed';
    }
}
