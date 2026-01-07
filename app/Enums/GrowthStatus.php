<?php

namespace App\Enums;

enum GrowthStatus: string
{
    case NORMAL = 'normal';
    case STUNTING = 'stunting';
    case OVERWEIGHT = 'overweight';
    case UNDERWEIGHT = 'underweight';

    public function label(): string
    {
        return match($this) {
            self::NORMAL => 'Normal',
            self::STUNTING => 'Stunting',
            self::OVERWEIGHT => 'Kelebihan Berat Badan',
            self::UNDERWEIGHT => 'Kekurangan Berat Badan',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::NORMAL => 'green',
            self::STUNTING => 'red',
            self::OVERWEIGHT => 'orange',
            self::UNDERWEIGHT => 'yellow',
        };
    }

    public function isHealthy(): bool
    {
        return $this === self::NORMAL;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
