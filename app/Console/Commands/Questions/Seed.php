<?php

namespace App\Console\Commands\Questions;

use App\Models\Module;
use App\Models\Question;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

#[Signature('questions:seed')]
#[Description('Load approved questions from _balanced.json into the database (upsert by external_id).')]
class Seed extends Command
{
    public function handle(): int
    {
        $balancedPath = base_path('database/data/questions/_balanced.json');

        if (! File::exists($balancedPath)) {
            $this->error('Run `php artisan questions:balance` first.');

            return self::FAILURE;
        }

        $balanced = json_decode(File::get($balancedPath), true);
        $externalIds = $balanced['external_ids'] ?? [];

        if (empty($externalIds)) {
            $this->warn('No questions in _balanced.json.');

            return self::SUCCESS;
        }

        $module = Module::firstOrCreate(
            ['slug' => 'm2-bsi-grundschutz'],
            [
                'name' => 'M2 - BSI Grundschutz',
                'description' => 'IT-Grundschutz-Praktiker Prüfungsvorbereitung nach BSI-Standards 200-1, 200-2, 200-3 und dem IT-Grundschutz-Kompendium.',
            ],
        );

        $index = $this->buildQuestionIndex();

        $created = 0;
        $updated = 0;
        $skipped = 0;

        $bar = $this->output->createProgressBar(count($externalIds));
        $bar->start();

        foreach ($externalIds as $externalId) {
            if (! isset($index[$externalId])) {
                $this->newLine();
                $this->warn("Question {$externalId} not found in any section file — skipping.");
                $skipped++;
                $bar->advance();

                continue;
            }

            $record = $index[$externalId];

            DB::transaction(function () use ($record, $module, &$created, &$updated) {
                $question = Question::updateOrCreate(
                    ['external_id' => $record['external_id']],
                    [
                        'module_id' => $module->id,
                        'text' => $record['text'],
                        'explanation' => $record['explanation'],
                        'quote' => $record['quote'],
                        'learning_objective' => $record['learning_objective'] ?? null,
                        'source_document' => $record['source_document'],
                        'source_chapter' => $record['source_chapter'],
                        'source_chapter_title' => $record['source_chapter_title'],
                        'source_page_start' => $record['source_page_start'],
                        'source_page_end' => $record['source_page_end'] ?? null,
                        'source_baustein_id' => $record['baustein_id'] ?? null,
                        'source_anforderung_type' => $record['anforderung_type'] ?? null,
                        'topic' => $record['topic'],
                        'difficulty' => $record['difficulty'],
                    ],
                );

                if ($question->wasRecentlyCreated) {
                    $created++;
                } else {
                    $question->answers()->delete();
                    $updated++;
                }

                foreach ($record['answers'] as $answer) {
                    $question->answers()->create([
                        'text' => $answer['text'],
                        'is_correct' => $answer['is_correct'],
                    ]);
                }
            });

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Done. Created: {$created}, Updated: {$updated}, Skipped: {$skipped}");
        $this->line('Total questions in DB: '.Question::count());

        return self::SUCCESS;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function buildQuestionIndex(): array
    {
        $basePath = base_path('database/data/questions');
        $index = [];

        foreach (['bsi_200_1', 'bsi_200_2', 'bsi_200_3', 'kompendium'] as $doc) {
            $docPath = $basePath.'/'.$doc;
            if (! is_dir($docPath)) {
                continue;
            }

            foreach (glob($docPath.'/*.json') as $file) {
                $basename = basename($file);
                if (str_contains($basename, '.review.') || str_starts_with($basename, '_')) {
                    continue;
                }

                $data = json_decode(File::get($file), true);
                if (! $data || ! isset($data['questions'])) {
                    continue;
                }

                $source = $data['source'] ?? [];

                foreach ($data['questions'] as $q) {
                    $index[$q['external_id']] = [
                        'external_id' => $q['external_id'],
                        'text' => $q['text'],
                        'explanation' => $q['explanation'],
                        'quote' => $q['quote'],
                        'learning_objective' => $q['learning_objective'] ?? null,
                        'source_document' => $source['document'] ?? $doc,
                        'source_chapter' => $source['chapter'] ?? '',
                        'source_chapter_title' => $source['chapter_title'] ?? '',
                        'source_page_start' => $q['page_start'] ?? $source['page_start'] ?? 1,
                        'source_page_end' => $q['page_end'] ?? null,
                        'baustein_id' => $q['baustein_id'] ?? null,
                        'anforderung_type' => $q['anforderung_type'] ?? null,
                        'topic' => $q['topic'],
                        'difficulty' => $q['difficulty'],
                        'answers' => $q['answers'],
                    ];
                }
            }
        }

        return $index;
    }
}
