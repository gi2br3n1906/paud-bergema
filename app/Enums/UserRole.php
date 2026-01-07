<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case TEACHER = 'teacher';
    case PARENT = 'parent';

    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Administrator',
            self::TEACHER => 'Guru',
            self::PARENT => 'Wali Murid',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
