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
        'score',
        'keywords',
        'narrative',
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

    public function scopeForReport($query, int $reportCardId)
    {
        return $query->where('report_card_id', $reportCardId);
    }

    public function scopeByScore($query, string $score)
    {
        return $query->where('score', $score);
    }

    // Helper methods

    public function getScoreLabel(): string
    {
        return match ($this->score) {
            'BB' => 'Belum Berkembang',
            'MB' => 'Mulai Berkembang',
            'BSH' => 'Berkembang Sesuai Harapan',
            'BSB' => 'Berkembang Sangat Baik',
            default => '-',
        };
    }

    public function getKeywordsArray(): array
    {
        if (empty($this->keywords)) {
            return [];
        }

        // Support both comma-separated and JSON format
        if (str_starts_with(trim($this->keywords), '[')) {
            return json_decode($this->keywords, true) ?? [];
        }

        return array_map('trim', explode(',', $this->keywords));
    }

    public function setKeywordsFromArray(array $keywords): void
    {
        $this->keywords = implode(', ', array_filter($keywords));
    }
}
