<?php

namespace App\DataTransferObjects\DailyLog;

class WorshipData
{
    public function __construct(
        public readonly bool $sholat_dhuha = false,
        public readonly bool $doa_harian = false,
        public readonly ?string $hafalan_surat = null,
        public readonly array $additional_activities = [],
    ) {}

    public static function from(array $data): self
    {
        return new self(
            sholat_dhuha: $data['sholat_dhuha'] ?? false,
            doa_harian: $data['doa_harian'] ?? false,
            hafalan_surat: $data['hafalan_surat'] ?? null,
            additional_activities: $data['additional_activities'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'sholat_dhuha' => $this->sholat_dhuha,
            'doa_harian' => $this->doa_harian,
            'hafalan_surat' => $this->hafalan_surat,
            'additional_activities' => $this->additional_activities,
        ];
    }
}
