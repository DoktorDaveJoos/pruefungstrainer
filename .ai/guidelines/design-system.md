# Design System — Prüfungstrainer

An exam-simulator for the official BSI IT-Grundschutz-Praktiker certification. The UI is **extremely clean, near-shadcn-default**: serious, academic, and optimized for long reading sessions. Tokens live in `resources/css/app.css`; conventions live here.

## Tokens (source of truth: `resources/css/app.css`)

Do not override these via `className`. Use the semantic Tailwind utilities that Tailwind v4 generates from them (`bg-primary`, `text-muted-foreground`, `bg-success`, etc.).

| Token                  | Purpose                                                                    |
| ---------------------- | -------------------------------------------------------------------------- |
| `primary` / `secondary`| Neutral-only brand palette. No colored accent.                             |
| `success`              | Pass state (≥60%), correct answers (paid review), confirmation toasts.     |
| `warning`              | Below-threshold score, weak-topic flags, non-error cautions.               |
| `destructive`          | Truly wrong answers, delete actions, error toasts. **Not used for "fail"** — failing an exam is not a system error. |
| `chart-1` … `chart-5`  | Topic breakdown on the results screen. Do not reuse for status.            |

## Typography

- **Font:** Instrument Sans, weights 400 / 500 / 600 / 700.
- **UI body (dashboard, settings, nav):** `text-sm` (14px) — shadcn default.
- **Exam question stems:** `text-lg` (18px) + `leading-relaxed`. Reading 50 German technical stems over 60 minutes needs breathing room.
- **Exam option labels:** `text-base` (16px).
- **Score & major stats (results screen):** `text-4xl` or larger with `font-bold` (weight 700). Only place weight 700 is used.
- **Max content width on exam/result screens:** `max-w-2xl` (≈42rem) centered. Keeps question line length under ~75 characters for readability.

## Numeric display — always use tabular figures

Apply `tabular-nums` to any number that updates in place or aligns in columns. Otherwise digits shift width and the UI jitters — unacceptable in a 60-minute timer.

Required on:

- **Timer** (`HH:MM:SS` countdown)
- **Progress** (`23 / 50`)
- **Score** (`30 / 50`, `60%`)
- **Topic accuracy percentages**

```tsx
<span className="tabular-nums">{formatTime(remaining)}</span>
```

## Status color semantics

| State                         | Component             | Token           |
| ----------------------------- | --------------------- | --------------- |
| Passed exam (≥60%)            | `Alert` / `Badge`     | `success`       |
| Below threshold (<60%)        | `Alert` / `Badge`     | `warning`       |
| Actual error, destructive op  | `Alert` / `Button`    | `destructive`   |
| Locked / paid-only feature    | `Badge variant="secondary"` + `Lock` icon | — |
| Correct answer (paid review)  | `bg-success/10 text-success` | `success` |
| Incorrect answer (paid review)| `bg-destructive/10 text-destructive` | `destructive` |

Never invent your own pass/fail colors. Never use raw Tailwind palette colors (`bg-green-500`, `text-red-600`).

## Focus rings — high contrast, always visible

`--ring` is bound to `--primary`, so focus rings are near-black in light mode and near-white in dark mode. This is deliberate: exam-takers navigate between options with Tab / Space / arrow keys, and accessibility matters more than aesthetic subtlety. Do not tone down focus rings per-component.

## Motion — restrained

- Transitions cap at **150ms** (`transition-colors`, `transition-opacity`). No longer.
- No transforms on hover. No scale/rotate animations.
- **One allowed "moment":** the score reveal on the results screen may use a single fade-up (≤300ms). Everything else is instant or near-instant.
- Skeletons and spinners follow shadcn defaults — do not customize their timing.

## Layout conventions

- **Exam screens:** centered column, `max-w-2xl`, question `Card` with generous `p-8` or `p-10`. Timer is fixed top-center or top-right, always visible.
- **Results screen:** `max-w-3xl`. Score hero at top, topic breakdown below (list of Progress bars or Chart), locked-preview Card below that, upgrade CTA last.
- **Dashboard / list screens:** follow shadcn defaults — standard `p-4` / `gap-4`, no custom width clamps.

## What we will *not* do

- No brand accent color. The palette stays neutral + semantic status.
- No radius changes. `--radius: 0.625rem` is the shadcn default and fits.
- No font-family swap. Instrument Sans is the chosen face.
- No emoji, no decorative icons beyond Lucide's `Clock`, `Flag`, `Check`, `X`, `Lock`, `ChevronRight`, etc.
- No custom `animate-*` for loading — use `Skeleton`.
- No `z-index` overrides on overlay components (Dialog, Sheet, Popover manage their own).

## Related files

- `resources/css/app.css` — token definitions
- `components.json` — shadcn config (style, base, icon library)
- `.ai/guidelines/shadcn-ui.md` — rule that all UI is shadcn components
