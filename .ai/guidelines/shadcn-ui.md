# UI Components — Always shadcn/ui

All UI in this project is built from shadcn/ui components. Do not hand-roll equivalents.

## Rules

- **Every UI element must be a shadcn component.** Before writing any markup, check if a shadcn component fits: `Button`, `Card`, `Dialog`, `Sheet`, `Alert`, `Empty`, `Badge`, `Skeleton`, `Separator`, `Tabs`, `Tooltip`, `Sidebar`, etc.
- **No custom-styled divs as UI primitives.** No styled `<span>` for badges, no `<hr>` or bordered divs for separators, no `animate-pulse` divs for loading — use `Badge`, `Separator`, `Skeleton`.
- **No raw HTML form controls.** Use `Input`, `Select`, `Checkbox`, `RadioGroup`, `Switch`, `Textarea`, `Label`, wrapped in `FieldGroup` + `Field`.
- **Callouts use `Alert`.** Empty states use `Empty`. Toasts use `sonner`'s `toast()`.
- **Compose, don't reinvent.** Settings page = `Tabs` + `Card` + form controls. Dashboard = `Sidebar` + `Card` + `Chart` + `Table`.

## Styling

- Use built-in variants (`variant="outline"`, `size="sm"`) before custom classes.
- Use semantic tokens: `bg-primary`, `text-muted-foreground`, `bg-background`. Never raw colors like `bg-blue-500`.
- `className` is for layout only — never override component colors or typography.
- `flex` + `gap-*` instead of `space-x-*` / `space-y-*`. `size-*` when width equals height.

## Workflow

1. Check the installed components list (see `components.json`) before importing.
2. If a needed component is not installed, add it: `npx shadcn@latest add <component>`.
3. Use `npx shadcn@latest docs <component>` to fetch current docs before using an unfamiliar component.
4. Never fetch shadcn source from GitHub manually — always use the CLI.

When in doubt, invoke the `shadcn` skill.
