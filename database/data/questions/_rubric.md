# BSI IT-Grundschutz-Praktiker — Question Generation Rubric

You are generating exam questions for an IT-Grundschutz-Praktiker prep platform. The only permissible source of truth is the BSI source text provided to you. Do not draw on outside knowledge of BSI standards — every claim must be verifiable from the passage.

## What makes a good question

- **Tests a concept**, not trivia. Good: "Welche Rolle trägt die Gesamtverantwortung für die Informationssicherheit?" Bad: "Wie viele Seiten hat Kapitel 3?"
- **Stem:** self-contained German sentence, 60–150 characters, ends with `?`.
- **4 answer options.** 1–3 marked correct. Scoring is all-or-nothing (all correct ticked, no incorrect ticked), so distractors must be wrong per *this passage* — not "arguably wrong elsewhere."
- **Distractors are plausible.** They should reflect likely misunderstandings, not obvious nonsense ("Blockchain", "Cloud", "42"). Wrong-but-tempting beats obviously-wrong.

## Quote rules (critical — the critic will check)

- `quote` must anchor EVERY correct answer. If correct answers come from different source bullets/sentences, concatenate them with ` • ` (space-bullet-space).
- Each fragment between ` • ` must be a verbatim substring of the source (after whitespace + soft-hyphen normalization).
- Natural full sentences. No cutting mid-sentence to avoid line breaks — the normalizer handles `\n` and `­\n` (soft-hyphen + newline → empty, newline alone → space, collapse).
- Fragments should flow in source order.

## Topic (exactly one, from this enum)

- `methodik` — IT-Grundschutz methodology, process phases, management roles, responsibilities, ISMS governance.
- `bausteine` — specific Bausteine and their application (SYS/APP/NET/INF/OPS/ORP/CON/IND/DER/ISMS).
- `risikoanalyse` — risk analysis methodology, BSI-Standard 200-3.
- `modellierung` — modelling, scope definition, Informationsverbund, asset mapping.
- `check` — IT-Grundschutz-Check, audit mechanics, test criteria.
- `standards` — content about the BSI standards themselves (lineage, scope, version history, document structure, certification framework).
- `notfall` — Notfallmanagement, BCM, recovery.
- `siem` — SIEM, monitoring, logging, SOC.

**Disambiguation:** use `methodik` for ISMS governance / roles / Chefsache content. Use `standards` only when the question is about a BSI document itself (e.g., "Which standard replaces 100-1?").

## Difficulty (exactly one)

- `basis` — factual, definitional, single-sentence paraphrase, direct application. The answer is stated explicitly in the passage.
- `experte` — requires cross-sentence synthesis, edge-case reasoning, distinguishing subtle cases, or numerical thresholds. NOT just a one-step paraphrase.

Default to `basis`. Only use `experte` if you can articulate why basic knowledge wouldn't suffice.

## external_id format

- Standards: `{doc-slug}-{chapter}-{3-digit-seq}` → `bsi-200-1-1.3-001`, `bsi-200-2-4.2-003`, etc.
- Kompendium: `kompendium-{baustein-id-lowercase}-{3-digit-seq}` → `kompendium-sys.1.1-001`.

## Output JSON schema (per section)

```json
{
    "source": {
        "document": "bsi_200_1",
        "chapter": "1.3",
        "chapter_title": "Adressatenkreis",
        "page_start": 6,
        "page_end": 7
    },
    "generated_at": "2026-04-16T...",
    "generator_model": "claude-opus-4-6[1m]",
    "questions": [
        {
            "external_id": "bsi-200-1-1.3-001",
            "text": "Welche... ?",
            "explanation": "Der BSI-Standard ... Die verbleibenden Optionen sind falsch, weil ...",
            "quote": "Exact verbatim sentence from source, possibly joined with ` • ` across fragments.",
            "topic": "methodik",
            "difficulty": "basis",
            "learning_objective": "Die ... benennen.",
            "baustein_id": null,
            "anforderung_type": null,
            "page_start": 6,
            "page_end": null,
            "answers": [
                { "text": "...", "is_correct": true },
                { "text": "...", "is_correct": false },
                { "text": "...", "is_correct": false },
                { "text": "...", "is_correct": false }
            ]
        }
    ]
}
```

- `baustein_id` and `anforderung_type` stay `null` for Standards documents.
- `page_start` / `page_end` on a question point to the page(s) where the *quote* lives (not the whole section). `page_end` is null if the quote is on a single page.

## Volume per section

Aim for **4–7 questions per section.** Short sections (a paragraph or two) may yield 2–3. Dense sections (multi-page conceptual content) can yield 7–8. Don't pad.

## JSON encoding

- Use proper German typography: opening `„`, closing `“` (never the straight ASCII `"` inside JSON strings, which breaks parsing).
- Preserve umlauts literally (ä, ö, ü, ß).
- File must be valid JSON — parse it before writing out.

## Final check before writing

For each question, mentally run the critic:
- Does the quote (after normalization) contain substrings supporting every correct answer?
- Is each distractor clearly wrong per the passage?
- Is the topic truly `methodik` vs `standards` vs etc.?
- Is the difficulty calibrated — `experte` needs real synthesis?

If any check fails, fix before writing.
