<?php

namespace App\DataTransferObjects\DailyLog;

class QuranProgressData
{
    public function __construct(
        public readonly int $iqro_level, // Jilid 1-6
        public readonly int $page_number,
        public readonly string $quality, // lancar, kurang_lancar, belum_lancar
        public readonly ?string $notes = null,
    ) {}

    public static function from(array $data): self
    {
        return new self(
            iqro_level: $data['iqro_level'] ?? 1,
            page_number: $data['page_number'] ?? 1,
            quality: $data['quality'] ?? 'lancar',
            notes: $data['notes'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'iqro_level' => $this->iqro_level,
            'page_number' => $this->page_number,
            'quality' => $this->quality,
            'notes' => $this->notes,
        ];
    }
}
