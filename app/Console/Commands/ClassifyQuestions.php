<?php

namespace App\Console\Commands;

use App\Models\Question;
use App\Services\QuestionClassifier;
use Illuminate\Console\Command;

class ClassifyQuestions extends Command
{
    protected $signature = 'questions:classify
        {--force : Reclassify questions that already have topic+difficulty set}
        {--limit= : Process at most N questions (useful for dry runs)}';

    protected $description = 'Classify questions by BSI topic + difficulty via Claude Haiku';

    public function handle(): int
    {
        $apiKey = config('services.anthropic.api_key');

        if (empty($apiKey)) {
            $this->error('ANTHROPIC_API_KEY is not set. Add it to your .env before running this command.');

            return self::FAILURE;
        }

        $classifier = new QuestionClassifier(
            apiKey: $apiKey,
            model: config('services.anthropic.model'),
            baseUrl: config('services.anthropic.base_url'),
        );

        $query = Question::query();

        if (! $this->option('force')) {
            $query->where(function ($q) {
                $q->whereNull('topic')->orWhereNull('difficulty');
            });
        }

        if ($limit = $this->option('limit')) {
            $query->limit((int) $limit);
        }

        $questions = $query->get();

        $this->info("Classifying {$questions->count()} questions…");

        if ($questions->isEmpty()) {
            $this->info('Nothing to do.');

            return self::SUCCESS;
        }

        $tagged = 0;
        $errors = 0;
        $bar = $this->output->createProgressBar($questions->count());
        $bar->start();

        foreach ($questions as $question) {
            $result = $classifier->classify($question);

            if ($result === null) {
                $errors++;
                $this->output->isVerbose() && $this->line("  ERR  question #{$question->id}");
            } else {
                $question->update([
                    'topic' => $result['topic'],
                    'difficulty' => $result['difficulty'],
                ]);
                $tagged++;
                $this->output->isVerbose() && $this->line("  OK   #{$question->id} → {$result['topic']->value} / {$result['difficulty']->value}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Tagged: {$tagged}");
        $this->info("Errors: {$errors}");

        return self::SUCCESS;
    }
}
