<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case HADIR = 'hadir';
    case SAKIT = 'sakit';
    case IZIN = 'izin';
    case ALPHA = 'alpha';

    public function label(): string
    {
        return match($this) {
            self::HADIR => 'Hadir',
            self::SAKIT => 'Sakit',
            self::IZIN => 'Izin',
            self::ALPHA => 'Alpha',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::HADIR => 'green',
            self::SAKIT => 'yellow',
            self::IZIN => 'blue',
            self::ALPHA => 'red',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
