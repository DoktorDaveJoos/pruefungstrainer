<?php

namespace App\Enums;

enum BsiTopic: string
{
    case Methodik = 'methodik';
    case Bausteine = 'bausteine';
    case Risikoanalyse = 'risikoanalyse';
    case Modellierung = 'modellierung';
    case Check = 'check';
    case Standards = 'standards';
    case Notfallmanagement = 'notfall';
    case Siem = 'siem';

    public function label(): string
    {
        return match ($this) {
            self::Methodik => 'IT-Grundschutz-Methodik',
            self::Bausteine => 'Bausteine',
            self::Risikoanalyse => 'Risikoanalyse',
            self::Modellierung => 'Modellierung',
            self::Check => 'IT-Grundschutz-Check',
            self::Standards => 'BSI-Standards',
            self::Notfallmanagement => 'Notfallmanagement',
            self::Siem => 'SIEM / Monitoring',
        };
    }

    /**
     * Topics excluded from exam draws, practice draws, and the guest free-tier pool.
     * Bausteine questions are too specific (SYS.x/APP.x/etc.) to realistically appear
     * in the official BSI-Praktiker exam at the asked granularity, so they are not
     * served — they remain in the database for potential reclassification later.
     *
     * @return list<self>
     */
    public static function disabled(): array
    {
        return [self::Bausteine];
    }

    public function isDisabled(): bool
    {
        return in_array($this, self::disabled(), strict: true);
    }
}
