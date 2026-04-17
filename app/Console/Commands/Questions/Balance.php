<?php

namespace App\Console\Commands\Questions;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

#[Signature('questions:balance')]
#[Description('Collect all approved questions into _balanced.json with distribution diagnostics.')]
class Balance extends Command
{
    // Reference distribution mirrors the official BSI Prüfungsbedingungen v1.2,
    // Tabelle 2 "Anzahl der Prüfungsfragen pro Themenfeld" (50-question exam).
    // Our 8-topic enum aggregates BSI's 15 Themenfelder (TF) as follows:
    //
    //   methodik      16 (32%) — TF#1 Einführung+Recht (2) + TF#4 Vorgehensweise Überblick (3)
    //                            + TF#6 Umsetzung Vorgehensweise [process portion] (4 of 8)
    //                            + TF#10 Aufrechterhaltung (5) + TF#12 Profile (2)
    //   bausteine      7 (14%) — TF#5 Kompendium-Überblick (2) + TF#9 Umsetzungsplanung (5)
    //   standards      7 (14%) — TF#2 Normen (2) + TF#3 Einführung IT-GS (2) + TF#11 Zertifizierung/ISO 27001 (3)
    //   check          6 (12%) — TF#7 IT-GS-Check (3) + TF#13 Vorbereitung Audit (3)
    //   risikoanalyse  5 (10%) — TF#8 Risikoanalyse (5)
    //   modellierung   4  (8%) — TF#6 Umsetzung Vorgehensweise [Strukturanalyse/Schutzbedarf/Modellierung phase] (4 of 8)
    //   siem           3  (6%) — TF#14 Sicherheitsvorfallbehandlung (3)
    //   notfall        2  (4%) — TF#15 BCM (2)
    public const REFERENCE_TOPIC_PCT = [
        'methodik' => 32,
        'bausteine' => 14,
        'standards' => 14,
        'check' => 12,
        'risikoanalyse' => 10,
        'modellierung' => 8,
        'siem' => 6,
        'notfall' => 4,
    ];

    // Per BSI Prüfungsbedingungen §7: the question pool should consist of 75% Basis-Fragen
    // (Level 1: Reproduktion, Reorganisation) and 25% Experten-Fragen (Level 2: Reproduktion,
    // Transfer, Reflexion).
    public const REFERENCE_DIFFICULTY_PCT = [
        'basis' => 75,
        'experte' => 25,
    ];

    public function handle(): int
    {
        $basePath = base_path('database/data/questions');
        $docs = ['bsi_200_1', 'bsi_200_2', 'bsi_200_3', 'kompendium'];

        $allQuestions = [];
        $distribution = [];

        foreach ($docs as $doc) {
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
                    $this->warn("Skipping invalid file: {$file}");

                    continue;
                }

                foreach ($data['questions'] as $q) {
                    $topic = $q['topic'] ?? 'unknown';
                    $difficulty = $q['difficulty'] ?? 'unknown';
                    $key = $topic.'_'.$difficulty;

                    $distribution[$key] = ($distribution[$key] ?? 0) + 1;
                    $allQuestions[] = [
                        'external_id' => $q['external_id'],
                        'source_file' => $file,
                        'document' => $doc,
                        'topic' => $topic,
                        'difficulty' => $difficulty,
                    ];
                }
            }
        }

        $total = count($allQuestions);
        $warnings = $this->computeWarnings($distribution, $total);

        $topicCounts = [];
        $difficultyCounts = [];
        $docCounts = [];

        foreach ($allQuestions as $q) {
            $topicCounts[$q['topic']] = ($topicCounts[$q['topic']] ?? 0) + 1;
            $difficultyCounts[$q['difficulty']] = ($difficultyCounts[$q['difficulty']] ?? 0) + 1;
            $docCounts[$q['document']] = ($docCounts[$q['document']] ?? 0) + 1;
        }

        $balanced = [
            'balanced_at' => now()->toIso8601String(),
            'actual_total' => $total,
            'by_topic' => $topicCounts,
            'by_difficulty' => $difficultyCounts,
            'by_document' => $docCounts,
            'distribution' => $distribution,
            'reference_blueprint' => [
                'topic_pct' => self::REFERENCE_TOPIC_PCT,
                'difficulty_pct' => self::REFERENCE_DIFFICULTY_PCT,
            ],
            'warnings' => $warnings,
            'external_ids' => array_column($allQuestions, 'external_id'),
        ];

        $outPath = $basePath.'/_balanced.json';
        File::put($outPath, json_encode($balanced, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n");

        $this->info("Wrote {$outPath}");
        $this->line("Total questions: {$total}");
        $this->newLine();

        $this->table(['Topic', 'Count', 'Actual %', 'Reference %'], collect($topicCounts)
            ->sortDesc()
            ->map(fn (int $count, string $topic) => [
                $topic,
                $count,
                round($count / $total * 100, 1).'%',
                (self::REFERENCE_TOPIC_PCT[$topic] ?? '?').'%',
            ])->values()->all());

        $this->newLine();
        $this->table(['Difficulty', 'Count', 'Actual %', 'Reference %'], collect($difficultyCounts)
            ->map(fn (int $count, string $diff) => [
                $diff,
                $count,
                round($count / $total * 100, 1).'%',
                (self::REFERENCE_DIFFICULTY_PCT[$diff] ?? '?').'%',
            ])->values()->all());

        foreach ($warnings as $w) {
            $this->warn($w);
        }

        return self::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function computeWarnings(array $distribution, int $total): array
    {
        $warnings = [];

        foreach (self::REFERENCE_TOPIC_PCT as $topic => $refPct) {
            $actual = 0;
            foreach ($distribution as $key => $count) {
                if (str_starts_with($key, $topic.'_')) {
                    $actual += $count;
                }
            }
            $actualPct = $total > 0 ? round($actual / $total * 100, 1) : 0;
            if ($actualPct < $refPct * 0.5) {
                $warnings[] = "Topic '{$topic}' significantly under reference: {$actualPct}% actual vs {$refPct}% target";
            }
        }

        foreach (self::REFERENCE_DIFFICULTY_PCT as $diff => $refPct) {
            $actual = 0;
            foreach ($distribution as $key => $count) {
                if (str_ends_with($key, '_'.$diff)) {
                    $actual += $count;
                }
            }
            $actualPct = $total > 0 ? round($actual / $total * 100, 1) : 0;
            if ($actualPct < $refPct * 0.5) {
                $warnings[] = "Difficulty '{$diff}' significantly under reference: {$actualPct}% actual vs {$refPct}% target";
            }
        }

        return $warnings;
    }
}
