<?php

namespace App\Enums;

enum DailyLogType: string
{
    case PRESENCE = 'presence';
    case WORSHIP = 'worship';
    case QURAN = 'quran';

    public function label(): string
    {
        return match($this) {
            self::PRESENCE => 'Presensi',
            self::WORSHIP => 'Mutaba\'ah Ibadah',
            self::QURAN => 'Jurnal Mengaji',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::PRESENCE => 'user-check',
            self::WORSHIP => 'heart',
            self::QURAN => 'book-open',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
