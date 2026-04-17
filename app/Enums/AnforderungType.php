<?php

namespace App\Enums;

enum AnforderungType: string
{
    case Basis = 'basis';
    case Standard = 'standard';
    case Hoch = 'hoch';

    public function label(): string
    {
        return match ($this) {
            self::Basis => 'Basis-Anforderung',
            self::Standard => 'Standard-Anforderung',
            self::Hoch => 'Anforderung bei erhöhtem Schutzbedarf',
        };
    }
}
