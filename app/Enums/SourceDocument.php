<?php

namespace App\Enums;

enum SourceDocument: string
{
    case Bsi2001 = 'bsi_200_1';
    case Bsi2002 = 'bsi_200_2';
    case Bsi2003 = 'bsi_200_3';
    case Kompendium = 'kompendium';

    public function label(): string
    {
        return match ($this) {
            self::Bsi2001 => 'BSI-Standard 200-1 (Managementsysteme für Informationssicherheit)',
            self::Bsi2002 => 'BSI-Standard 200-2 (IT-Grundschutz-Methodik)',
            self::Bsi2003 => 'BSI-Standard 200-3 (Risikoanalyse auf der Basis von IT-Grundschutz)',
            self::Kompendium => 'IT-Grundschutz-Kompendium',
        };
    }

    public function citationPrefix(): string
    {
        return match ($this) {
            self::Bsi2001 => 'BSI-Standard 200-1',
            self::Bsi2002 => 'BSI-Standard 200-2',
            self::Bsi2003 => 'BSI-Standard 200-3',
            self::Kompendium => 'IT-Grundschutz-Kompendium',
        };
    }

    public function isKompendium(): bool
    {
        return $this === self::Kompendium;
    }
}
