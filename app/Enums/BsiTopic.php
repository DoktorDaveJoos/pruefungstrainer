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
}
