<?php

namespace App\Enums;

enum StatusVisitasi: string
{
    case Terjadwal = 'terjadwal';
    case Berlangsung = 'berlangsung';
    case Selesai = 'selesai';
    case Dibatalkan = 'dibatalkan';

    public function label(): string
    {
        return match ($this) {
            self::Terjadwal => 'Terjadwal',
            self::Berlangsung => 'Berlangsung',
            self::Selesai => 'Selesai',
            self::Dibatalkan => 'Dibatalkan',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Terjadwal => 'bg-secondary',
            self::Berlangsung => 'bg-info text-dark',
            self::Selesai => 'bg-success',
            self::Dibatalkan => 'bg-danger',
        };
    }
}
