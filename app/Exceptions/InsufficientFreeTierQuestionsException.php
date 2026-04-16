<?php

namespace App\Exceptions;

use RuntimeException;

class InsufficientFreeTierQuestionsException extends RuntimeException
{
    public static function noQuestionsFlagged(): self
    {
        return new self('No questions flagged as free tier — run `php artisan exam:flag-free-tier` to seed the pool.');
    }
}
