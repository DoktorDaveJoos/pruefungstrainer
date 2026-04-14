<?php

namespace App\Enums;

enum QuestionDifficulty: string
{
    case Basis = 'basis';
    case Experte = 'experte';

    public function label(): string
    {
        return match ($this) {
            self::Basis => 'Basis',
            self::Experte => 'Experte',
        };
    }
}
