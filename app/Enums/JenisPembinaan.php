<?php

namespace App\Enums;

enum JenisPembinaan: string
{
    case Pertama = 'Peringatan Pertama';
    case Kedua = 'Peringatan Kedua';
    case Terakhir = 'Peringatan Terakhir';

    public function level(): int
    {
        return match ($this) {
            self::Pertama => 1,
            self::Kedua => 2,
            self::Terakhir => 3,
        };
    }

    public function spCode(): string
    {
        return (string) $this->level();
    }
}
