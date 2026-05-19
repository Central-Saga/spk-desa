<?php

namespace App\Enums;

enum StatusPeriode: string
{
    case Draft = 'draft';
    case Aktif = 'aktif';
    case Selesai = 'selesai';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Aktif => 'Aktif',
            self::Selesai => 'Selesai',
        };
    }
}
