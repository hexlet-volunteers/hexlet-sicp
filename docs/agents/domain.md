# Domain Docs

How the engineering skills should consume this repo's domain documentation when exploring the codebase.

Layout: **single-context** — one `CONTEXT.md` plus `docs/adr/` at the repo root. No monorepo signals (no `workspaces`, no `pnpm-workspace.yaml`, no `packages/*`).

## Before exploring, read these

- **`CONTEXT.md`** at the repo root — глоссарий. Доменные модели там не дублируются: они описаны в разделе «Домен (app/Models)» в `AGENTS.md`. Новые термины дописывает `/domain-modeling`, когда они реально требуют определения.
- **`docs/adr/`** — read ADRs that touch the area you're about to work in.
- **`AGENTS.md`** at the repo root (`CLAUDE.md` — симлинк на него) — команды, архитектура, конвенции.

If any of these files don't exist, **proceed silently**. Don't flag their absence; don't suggest creating them upfront. The `/domain-modeling` skill (reached via `/grill-with-docs` and `/improve-codebase-architecture`) creates them lazily when terms or decisions actually get resolved.

## Existing ADRs

| ADR | О чём |
|---|---|
| [0001](../adr/0001-frontend-strangler-migration.md) | постраничная миграция фронтенда на Inertia + Mantine; два CSS-фреймворка как сознательное переходное состояние |
| [0002](../adr/0002-no-ziggy-urls-from-backend.md) | URL приходят с бэкенда, Ziggy не используется (префикс локали) |
| [0003](../adr/0003-php-owns-translations.md) | PHP — единственный источник переводов, i18next их только потребляет |
| [0004](../adr/0004-ssr-deferred-seo-debt.md) | SSR отложен; публичные страницы остаются на Blade; принятый SEO-долг на трёх страницах |
| [0005](../adr/0005-code-owns-testops-case-names.md) | код владеет именем автоматизированного тест-кейса в Allure TestOps; ручные имена заливка **не** перетирает — см. «Наблюдённое поведение» |

Первые четыре касаются фронтенда. Любая работа в `resources/js/**`, `resources/views/**`, `vite.config.js` или `app/Http/Middleware/HandleInertiaRequests.php` должна их учитывать; дополнительно есть подробный план в [docs/frontend-migration.md](../frontend-migration.md). ADR-0005 касается отчётности по тестам: работа в `phpunit.xml`, `tests/**` и `.github/workflows/**` должна его учитывать, снимок ручной разметки — в [docs/testops-manual-names.md](../testops-manual-names.md).

## Use the glossary's vocabulary

When your output names a domain concept (in an issue title, a refactor proposal, a hypothesis, a test name), use the term as defined in `CONTEXT.md`. Don't drift to synonyms the glossary explicitly avoids.

Доменные модели живут не в `CONTEXT.md`, а в разделе «Домен (app/Models)» в `AGENTS.md`: **Chapter**, **Exercise**, **ExerciseMember**, **ChapterMember**, **Solution**, **Comment**, **Activity**. Термины употребляются на английском даже в русскоязычных issue.

В `CONTEXT.md` сейчас один раздел — «Отчётность по тестам»: **Launch**, **тест-результат**, **тест-кейс** (автоматизированный и ручной), **UI-заготовка**, **test layer**. Границы там неочевидные, и путать их легко: «тест-кейс» — не синоним «теста», а UI-заготовка без запусков — норма, а не пробел в покрытии.

If the concept you need isn't in the glossary yet, that's a signal — either you're inventing language the project doesn't use (reconsider) or there's a real gap (note it for `/domain-modeling`).

## Flag ADR conflicts

If your output contradicts an existing ADR, surface it explicitly rather than silently overriding:

> _Contradicts ADR-0002 (URL приходят с бэкенда) — but worth reopening because…_
