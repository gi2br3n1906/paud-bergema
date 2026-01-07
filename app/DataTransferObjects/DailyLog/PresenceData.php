<?php

namespace App\DataTransferObjects\DailyLog;

class PresenceData
{
    public function __construct(
        public readonly string $status, // hadir, sakit, izin, alpha
        public readonly ?string $arrival_time = null,
        public readonly ?string $departure_time = null,
    ) {}

    public static function from(array $data): self
    {
        return new self(
            status: $data['status'] ?? 'hadir',
            arrival_time: $data['arrival_time'] ?? null,
            departure_time: $data['departure_time'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'arrival_time' => $this->arrival_time,
            'departure_time' => $this->departure_time,
        ];
    }
}
