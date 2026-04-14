<?php

namespace App\Models;

use Database\Factories\ExamAttemptFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'session_uuid',
    'started_at',
    'timer_expires_at',
    'submitted_at',
    'total_questions',
    'score',
    'is_free_attempt',
])]
class ExamAttempt extends Model
{
    /** @use HasFactory<ExamAttemptFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'timer_expires_at' => 'datetime',
            'submitted_at' => 'datetime',
            'total_questions' => 'integer',
            'score' => 'integer',
            'is_free_attempt' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function examAnswers(): HasMany
    {
        return $this->hasMany(ExamAnswer::class);
    }

    public function isSubmitted(): bool
    {
        return $this->submitted_at !== null;
    }

    public function isAnonymous(): bool
    {
        return $this->user_id === null;
    }

    public function hasExpired(): bool
    {
        return $this->timer_expires_at->isPast();
    }
}
