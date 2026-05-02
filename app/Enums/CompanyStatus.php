<?php

namespace App\Enums;

enum CompanyStatus: string
{
    case Active = 'active';
    case Passive = 'passive';
    case Trial = 'trial';
    case Suspended = 'suspended';

    public function label(): string
    {
        return match($this) {
            self::Active => 'Aktif',
            self::Passive => 'Pasif (Ödeme Bekliyor)',
            self::Trial => 'Deneme Sürümü',
            self::Suspended => 'Askıya Alındı',
        };
    }
}
