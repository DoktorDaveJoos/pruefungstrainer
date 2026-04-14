<?php

namespace Database\Seeders;

use App\Models\Answer;
use App\Models\Module;
use App\Models\Question;
use Illuminate\Database\Seeder;

class SampleQuestionsSeeder extends Seeder
{
    public function run(): void
    {
        $module = Module::firstOrCreate(
            ['slug' => 'm2-bsi-grundschutz'],
            [
                'name' => 'M2 - BSI Grundschutz',
                'description' => 'Fragen zum BSI IT-Grundschutz und den zugehörigen Standards.',
            ]
        );

        $questions = [
            [
                'text' => 'Welche Dokumente sind für den Einstieg in den IT-Grundschutz erforderlich oder hilfreich?',
                'explanation' => 'Der BSI-Standard 200-2 beschreibt die IT-Grundschutz-Methodik und das IT-Grundschutz-Kompendium enthält die Bausteine mit Anforderungen. Der BSI-Standard 100-4 ist veraltet (ersetzt durch 200-4) und die BSI TR-03161 behandelt ein anderes Thema.',
                'quote' => 'Hierfür liefern die IT-Grundschutz-Methodik und das IT-Grundschutz-Kompendium zentrale Hinweise und praktische Umsetzungshilfen.',
                'source' => 'BSI-Standard 200-2, Kapitel 1.4, S. 9',
                'answers' => [
                    ['text' => 'BSI-Standard 200-2', 'is_correct' => true],
                    ['text' => 'IT-Grundschutz-Kompendium', 'is_correct' => true],
                    ['text' => 'BSI-Standard 100-4', 'is_correct' => false],
                    ['text' => 'BSI TR-03161', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Was beschreibt der BSI-Standard 200-1?',
                'explanation' => 'Der BSI-Standard 200-1 definiert die allgemeinen Anforderungen an ein Managementsystem für Informationssicherheit (ISMS). Er beschreibt nicht die Vorgehensweise der IT-Grundschutz-Methodik (das ist 200-2) und auch nicht das Notfallmanagement (200-4).',
                'quote' => 'Im BSI-Standard 200-1 Managementsysteme für Informationssicherheit (ISMS) wird beschrieben, mit welchen Methoden Informationssicherheit in einer Institution generell initiiert und gesteuert werden kann.',
                'source' => 'BSI-Standard 200-2, Kapitel 1.4, S. 9',
                'answers' => [
                    ['text' => 'Anforderungen an ein ISMS', 'is_correct' => true],
                    ['text' => 'Die IT-Grundschutz-Vorgehensweise', 'is_correct' => false],
                    ['text' => 'Notfallmanagement', 'is_correct' => false],
                    ['text' => 'Kryptographische Verfahren', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Welche Schutzbedarfskategorien kennt der IT-Grundschutz?',
                'explanation' => 'Der IT-Grundschutz definiert drei Schutzbedarfskategorien: normal, hoch und sehr hoch. Die Kategorie "kritisch" existiert im IT-Grundschutz nicht.',
                'quote' => '„normal" Die Schadensauswirkungen sind begrenzt und überschaubar. „hoch" Die Schadensauswirkungen können beträchtlich sein. „sehr hoch" Die Schadensauswirkungen können ein existenziell bedrohliches, katastrophales Ausmaß erreichen.',
                'source' => 'BSI-Standard 200-2, Kapitel 8.2.1, S. 104',
                'answers' => [
                    ['text' => 'Normal', 'is_correct' => true],
                    ['text' => 'Hoch', 'is_correct' => true],
                    ['text' => 'Sehr hoch', 'is_correct' => true],
                    ['text' => 'Kritisch', 'is_correct' => false],
                ],
            ],
        ];

        foreach ($questions as $questionData) {
            $answers = $questionData['answers'];
            unset($questionData['answers']);

            $question = Question::create([
                ...$questionData,
                'module_id' => $module->id,
            ]);

            foreach ($answers as $answerData) {
                Answer::create([
                    ...$answerData,
                    'question_id' => $question->id,
                ]);
            }
        }
    }
}
