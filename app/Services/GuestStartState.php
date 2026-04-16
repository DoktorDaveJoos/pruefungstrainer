<?php

namespace App\Services;

class GuestStartState
{
    private function __construct(
        public readonly GuestStartStatus $status,
        public readonly ?int $attemptId,
    ) {}

    public static function available(): self
    {
        return new self(GuestStartStatus::Available, null);
    }

    public static function resume(int $attemptId): self
    {
        return new self(GuestStartStatus::Resume, $attemptId);
    }

    public static function alreadyDone(int $attemptId): self
    {
        return new self(GuestStartStatus::AlreadyDone, $attemptId);
    }
}
