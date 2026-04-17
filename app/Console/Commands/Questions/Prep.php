<?php

namespace App\Console\Commands\Questions;

use App\Enums\SourceDocument;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

#[Signature('questions:prep {document}')]
#[Description('Parse a BSI source .md into a chapter/section manifest used by the question generation walk.')]
class Prep extends Command
{
    public function handle(): int
    {
        $document = SourceDocument::tryFrom((string) $this->argument('document'));

        if ($document === null) {
            $this->error('Unknown document. Expected one of: '.implode(', ', array_column(SourceDocument::cases(), 'value')));

            return self::FAILURE;
        }

        $mdPath = $this->mdPath($document);

        if (! File::exists($mdPath)) {
            $this->error("Source Markdown not found: {$mdPath}");
            $this->line('Run `pdftotext -layout -nopgbrk <pdf> <md>` first.');

            return self::FAILURE;
        }

        $text = File::get($mdPath);

        $sections = $document === SourceDocument::Kompendium
            ? $this->parseKompendium($text)
            : $this->parseStandard($text);

        $manifestPath = $this->manifestPath($document);
        $manifest = [
            'document' => $document->value,
            'title' => $document->label(),
            'source_pdf' => $this->pdfRelativePath($document),
            'source_md' => $this->mdRelativePath($document),
            'generated_at' => now()->toIso8601String(),
            'sections' => $sections,
        ];

        File::put(
            $manifestPath,
            json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n"
        );

        $this->info("Wrote {$manifestPath}");
        $this->line('Sections: '.count($sections));

        return self::SUCCESS;
    }

    /**
     * @return list<array{chapter: string, title: string, page_start: int, page_end: ?int, char_start: int, char_end: int}>
     */
    public function parseStandard(string $text): array
    {
        [$bodyStart, $bodyText] = $this->findBodyStart($text);

        $headings = $this->findStandardHeadings($bodyText, $bodyStart);
        $pageMarkers = $this->findPageMarkers($bodyText, $bodyStart);

        $headings = $this->dedupeRunningHeaders($headings);
        $sections = $this->buildSections($headings, $pageMarkers, strlen($text));

        return array_values(array_filter(
            $sections,
            fn (array $s): bool => $this->isLevelTwoOrStandaloneLevelOne($s, $sections)
        ));
    }

    /**
     * @return list<array{chapter: string, title: string, page_start: int, page_end: ?int, char_start: int, char_end: int}>
     */
    public function parseKompendium(string $text): array
    {
        // Kompendium TOC entries share the heading shape ("SYS.1.1 Allgemeiner Server ..."),
        // so we don't skip TOC by offset — instead the heading filter + "Beschreibung" follow-up
        // check separates real Baustein headings from TOC rows and running headers.
        $bodyStart = 0;
        $bodyText = $text;

        $headings = $this->findBausteinHeadings($bodyText, $bodyStart);

        // Kompendium pages reset per Baustein and its first page has no printed footer.
        // Citations use the Baustein-ID, not the page number, so we don't track pages here.
        $sections = [];
        $count = count($headings);
        $totalLen = strlen($text);

        foreach ($headings as $i => $h) {
            $charStart = $h['offset'];
            $charEnd = $i + 1 < $count ? $headings[$i + 1]['offset'] : $totalLen;

            $sections[] = [
                'chapter' => $h['chapter'],
                'title' => $h['title'],
                'page_start' => 1,
                'page_end' => null,
                'char_start' => $charStart,
                'char_end' => $charEnd,
            ];
        }

        return $sections;
    }

    /**
     * @return list<array{chapter: string, title: string, offset: int}>
     */
    private function findStandardHeadings(string $bodyText, int $bodyStart): array
    {
        $headings = [];
        if (preg_match_all('/^(\d{1,2}(?:\.\d{1,2}){0,2})\s+([A-ZÄÖÜ][^\n]+?)$/mu', $bodyText, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $i => [$line, $offset]) {
                $chapter = $matches[1][$i][0];
                $title = trim($matches[2][$i][0]);

                if ($this->looksLikeTocOrNoise($title)) {
                    continue;
                }

                if ($this->looksLikeMidSentence($title)) {
                    continue;
                }

                if (! $this->isPlausibleStandardChapter($chapter)) {
                    continue;
                }

                $headings[] = [
                    'chapter' => $chapter,
                    'title' => $title,
                    'offset' => $offset + $bodyStart,
                ];
            }
        }

        return $headings;
    }

    /**
     * BSI standards have at most ~15 top-level chapters. Level-1 numbers outside that range are
     * almost always page numbers that happened to match the heading regex.
     */
    private function isPlausibleStandardChapter(string $chapter): bool
    {
        $firstPart = (int) explode('.', $chapter)[0];

        return $firstPart >= 1 && $firstPart <= 15;
    }

    /**
     * Real Baustein headings in the Kompendium have the form `SYS.1.1 Allgemeiner Server` and are
     * followed within ~300 chars by the standard section "1 Beschreibung" (or "1. Beschreibung").
     * Running headers reference the parent Schicht as "SYS.1: Server" — those are filtered here.
     *
     * @return list<array{chapter: string, title: string, offset: int}>
     */
    private function findBausteinHeadings(string $bodyText, int $bodyStart): array
    {
        $headings = [];
        if (preg_match_all('/^([A-Z]{3,4}(?:\.\d+)+)\s+([A-ZÄÖÜ][^\n]+?)$/mu', $bodyText, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $i => [$line, $offset]) {
                $baustein = $matches[1][$i][0];
                $title = trim($matches[2][$i][0]);

                if (! $this->isValidBausteinTitle($title)) {
                    continue;
                }

                // Confirm by requiring "1. Beschreibung" to appear *before* any other Baustein-heading
                // candidate. TOC entries point into the same file — if another real heading is encountered
                // first, the current match is a TOC row and should be rejected.
                $lookAhead = substr($bodyText, $offset + strlen($matches[0][$i][0]), 400);
                $beschreibungPos = preg_match('/\b1\.?\s*Beschreibung\b/u', $lookAhead, $bm, PREG_OFFSET_CAPTURE)
                    ? $bm[0][1]
                    : PHP_INT_MAX;
                $nextHeadingPos = preg_match('/^[A-Z]{3,4}(?:\.\d+)+\s+[A-ZÄÖÜ]/mu', $lookAhead, $hm, PREG_OFFSET_CAPTURE)
                    ? $hm[0][1]
                    : PHP_INT_MAX;

                if ($beschreibungPos === PHP_INT_MAX || $beschreibungPos > $nextHeadingPos) {
                    continue;
                }

                $headings[] = [
                    'chapter' => $baustein,
                    'title' => $title,
                    'offset' => $offset + $bodyStart,
                ];
            }
        }

        // Kompendium: dedupe by chapter only (one real heading per Baustein).
        $seen = [];
        $out = [];
        foreach ($headings as $h) {
            if (isset($seen[$h['chapter']])) {
                continue;
            }
            $seen[$h['chapter']] = true;
            $out[] = $h;
        }

        return $out;
    }

    private function isValidBausteinTitle(string $title): bool
    {
        if ($this->looksLikeTocOrNoise($title)) {
            return false;
        }

        // Running-header pattern "SYS.1: Server" / "ORP: Organisation" references a Schicht ID.
        if (preg_match('/^[A-Z]{3,4}(\.\d+)*\s*:/', $title)) {
            return false;
        }

        // Broken-line hyphen suffix means this is a mid-sentence wrap, not a heading.
        if (str_ends_with($title, '-')) {
            return false;
        }

        // Real Baustein titles are concise (<= 70 chars in practice).
        if (mb_strlen($title) > 70) {
            return false;
        }

        return true;
    }

    /**
     * Two page-footer formats in practice:
     *   1. Standards (200-X): bare number on its own line (`^\s*\d+\s*$`).
     *   2. Kompendium: `{page_num}{spaces}IT-Grundschutz-Kompendium: Stand Februar 2023`.
     *
     * Both are detected here. Page numbers in the Kompendium reset per Baustein.
     *
     * @return list<array{page: int, offset: int}>
     */
    private function findPageMarkers(string $bodyText, int $bodyStart): array
    {
        $pageMarkers = [];

        // Standards footer: bare number.
        if (preg_match_all('/^\s*(\d{1,4})\s*$/mu', $bodyText, $bareMatches, PREG_OFFSET_CAPTURE)) {
            foreach ($bareMatches[0] as $i => [$line, $offset]) {
                $pageMarkers[] = [
                    'page' => (int) $bareMatches[1][$i][0],
                    'offset' => $offset + $bodyStart,
                ];
            }
        }

        // Kompendium footer: "{n}  IT-Grundschutz-Kompendium: Stand ..."
        if (preg_match_all('/^(\d{1,4})\s{2,}IT-Grundschutz-Kompendium/mu', $bodyText, $kompMatches, PREG_OFFSET_CAPTURE)) {
            foreach ($kompMatches[0] as $i => [$line, $offset]) {
                $pageMarkers[] = [
                    'page' => (int) $kompMatches[1][$i][0],
                    'offset' => $offset + $bodyStart,
                ];
            }
        }

        usort($pageMarkers, fn ($a, $b) => $a['offset'] <=> $b['offset']);

        return $pageMarkers;
    }

    /**
     * The body of each Standards document starts right after the TOC. TOC entries uniquely contain
     * a dot-leader pattern (`. . . . . . N`) — find the LAST line carrying that pattern and start
     * the body immediately after it.
     *
     * @return array{0: int, 1: string}
     */
    private function findBodyStart(string $text): array
    {
        $lastTocEnd = 0;

        if (preg_match_all('/\.{4,}\s*\d{1,4}\s*$/m', $text, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as [$line, $offset]) {
                $lineEnd = strpos($text, "\n", $offset);
                if ($lineEnd !== false && $lineEnd > $lastTocEnd) {
                    $lastTocEnd = $lineEnd + 1;
                }
            }
        }

        return [$lastTocEnd, substr($text, $lastTocEnd)];
    }

    private function looksLikeTocOrNoise(string $title): bool
    {
        if (preg_match('/\.{4,}/', $title)) {
            return true;
        }
        if (preg_match('/\s{3,}\d{1,4}$/', $title)) {
            return true;
        }

        return false;
    }

    /**
     * Filters mid-sentence fragments that a naïve regex match picks up — e.g., text-wrapped lines
     * that happen to start with a digit pattern. Heading titles are concise noun phrases; mid-sentence
     * fragments tend to end with a period followed by nothing, or contain more than one sentence.
     */
    private function looksLikeMidSentence(string $title): bool
    {
        // Heading titles end with a letter, a closing paren, a digit in a model-name, or a plain letter.
        // Mid-sentence fragments often end with a period, comma, or "ab." / "ebenfalls." etc.
        if (preg_match('/\b[a-zäöüß]+\.$/u', $title)) {
            return true;
        }

        // A real heading should not contain multiple periods (except inside Baustein-IDs which aren't used here).
        $periodCount = substr_count($title, '.');
        if ($periodCount >= 2) {
            return true;
        }

        // Long titles (>120 chars) are almost always mid-sentence spillovers.
        if (mb_strlen($title) > 120) {
            return true;
        }

        return false;
    }

    /**
     * Running headers of a following section often appear at the top of a page whose body still
     * belongs to the previous section — so the running header sits earlier in the extracted text
     * than the real body heading. We dedupe by chapter and keep the LAST occurrence, which is
     * the real body heading.
     *
     * @param  list<array{chapter: string, title: string, offset: int}>  $headings
     * @return list<array{chapter: string, title: string, offset: int}>
     */
    private function dedupeRunningHeaders(array $headings): array
    {
        $byChapter = [];
        foreach ($headings as $h) {
            $byChapter[$h['chapter']] = $h;
        }

        $out = array_values($byChapter);
        usort($out, fn (array $a, array $b): int => $a['offset'] <=> $b['offset']);

        return $out;
    }

    /**
     * @param  list<array{chapter: string, title: string, offset: int}>  $headings
     * @param  list<array{page: int, offset: int}>  $pageMarkers
     * @return list<array{chapter: string, title: string, page_start: int, page_end: ?int, char_start: int, char_end: int}>
     */
    private function buildSections(array $headings, array $pageMarkers, int $totalLen): array
    {
        $sections = [];
        $count = count($headings);

        foreach ($headings as $i => $h) {
            $charStart = $h['offset'];
            $charEnd = $i + 1 < $count ? $headings[$i + 1]['offset'] : $totalLen;

            $sections[] = [
                'chapter' => $h['chapter'],
                'title' => $h['title'],
                'page_start' => $this->pageAt($pageMarkers, $charStart),
                'page_end' => $this->pageAt($pageMarkers, $charEnd - 1),
                'char_start' => $charStart,
                'char_end' => $charEnd,
            ];
        }

        return $sections;
    }

    /**
     * Page numbers in the source PDFs appear at the END of each page (footer). So a given offset
     * belongs to the page whose marker comes *next* after it.
     *
     * @param  list<array{page: int, offset: int}>  $pageMarkers
     */
    private function pageAt(array $pageMarkers, int $offset): int
    {
        foreach ($pageMarkers as $m) {
            if ($m['offset'] >= $offset) {
                return $m['page'];
            }
        }

        return count($pageMarkers) > 0 ? end($pageMarkers)['page'] : 1;
    }

    /**
     * @param  array{chapter: string}  $section
     * @param  list<array{chapter: string}>  $all
     */
    private function isLevelTwoOrStandaloneLevelOne(array $section, array $all): bool
    {
        $depth = substr_count($section['chapter'], '.') + 1;

        if ($depth === 2) {
            return true;
        }

        if ($depth === 3) {
            return false;
        }

        $prefix = $section['chapter'].'.';
        foreach ($all as $other) {
            if (str_starts_with($other['chapter'], $prefix)) {
                return false;
            }
        }

        return true;
    }

    private function mdPath(SourceDocument $doc): string
    {
        return base_path('storage/app/private/files/'.$this->mdFilename($doc));
    }

    private function mdRelativePath(SourceDocument $doc): string
    {
        return 'storage/app/private/files/'.$this->mdFilename($doc);
    }

    private function manifestPath(SourceDocument $doc): string
    {
        $base = 'storage/app/private/files/'.$this->mdFilename($doc);

        return base_path(str_replace('.md', '.manifest.json', $base));
    }

    private function pdfRelativePath(SourceDocument $doc): string
    {
        return 'storage/app/private/files/'.match ($doc) {
            SourceDocument::Bsi2001 => 'standard_200_1.pdf',
            SourceDocument::Bsi2002 => 'standard_200_2.pdf',
            SourceDocument::Bsi2003 => 'standard_200_3.pdf',
            SourceDocument::Kompendium => 'IT_Grundschutz_Kompendium_Edition2023 (1).pdf',
        };
    }

    private function mdFilename(SourceDocument $doc): string
    {
        return match ($doc) {
            SourceDocument::Bsi2001 => 'standard_200_1.md',
            SourceDocument::Bsi2002 => 'standard_200_2.md',
            SourceDocument::Bsi2003 => 'standard_200_3.md',
            SourceDocument::Kompendium => 'kompendium.md',
        };
    }
}
