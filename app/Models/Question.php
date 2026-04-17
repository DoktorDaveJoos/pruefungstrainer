<?php

namespace App\Models;

use App\Enums\AnforderungType;
use App\Enums\BsiTopic;
use App\Enums\QuestionDifficulty;
use App\Enums\SourceDocument;
use Database\Factories\QuestionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'external_id',
    'module_id',
    'text',
    'explanation',
    'quote',
    'learning_objective',
    'source_document',
    'source_chapter',
    'source_chapter_title',
    'source_page_start',
    'source_page_end',
    'source_baustein_id',
    'source_anforderung_type',
    'topic',
    'difficulty',
    'is_free_tier',
])]
class Question extends Model
{
    /** @use HasFactory<QuestionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'topic' => BsiTopic::class,
            'difficulty' => QuestionDifficulty::class,
            'source_document' => SourceDocument::class,
            'source_anforderung_type' => AnforderungType::class,
            'source_page_start' => 'integer',
            'source_page_end' => 'integer',
            'is_free_tier' => 'boolean',
        ];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    protected function sourceCitation(): Attribute
    {
        return Attribute::get(function (): string {
            $prefix = $this->source_document->citationPrefix();

            if ($this->source_baustein_id !== null) {
                return "{$prefix}, {$this->source_baustein_id}";
            }

            return "{$prefix}, Kapitel {$this->source_chapter}, S. {$this->source_page_start}";
        });
    }
}
