# AGENTS.md

This file provides guidance to AI coding agents (Claude Code, Codex, Cursor, Aider, Copilot, and others) working with this repository.

## Project

Hexlet SICP — трекер изучения книги SICP. Пользователи читают главы (иерархическое дерево), решают упражнения на Scheme/Racket и отслеживают прогресс с лидербордами. Стек: **Laravel 13 (PHP 8.3+), Inertia.js + React 19 + Vite, Blade, SQLite (local) / PostgreSQL (prod)**.

## Commands

Всё запускается через Makefile. Локально (SQLite + локальный PHP):

- `make setup` — полная инициализация (env, sqlite, install, key, миграции+сиды, ide-helper, сборка фронта)
- `make start` — поднять сервер (heroku local -f Procfile.dev) на http://127.0.0.1:8000
- `make start-app` — только PHP-сервер; `make start-frontend` — только Vite dev
- `make test` — тесты (`php artisan test`)
- `make lint` — lint JS + PHP; `make lint-fix` — автофикс (phpcbf для PHP, Biome для JS, prettier для blade)
- `make db-prepare` — `migrate:fresh --force --seed`
- `make cache-clear` — сброс config/cache/view (нужно при `CSRF token mismatch`)

Тесты (phpunit, три testsuite — Unit / Feature / Exercises, по умолчанию Feature):

- Один файл: `vendor/bin/phpunit tests/Feature/Http/Controllers/HomeControllerTest.php`
- Один метод: `vendor/bin/phpunit --filter testIndex`
- Testsuite: `vendor/bin/phpunit --testsuite Unit` (или Feature / Exercises)
- Проверка teacher-решений: `make test-solutions` (composer exec phpunit -- --testsuite Exercises)

Линтеры: `make lint-php` (phpcs, PSR-12 + Slevomat), `make lint-js` (Biome). Перед push хук `pre-push-hook` гоняет lint+analyse.

Docker: команды с префиксом `compose-*` в `make-compose.mk` (например `make compose-test`, `make compose-setup`). PostgreSQL локально: `make compose-start-database` + `make db-prepare`.

## Architecture

### Домен (app/Models)

- **Chapter** — главы SICP в дереве (`parent_id`, `path` вида "1.1.2"). Читаема только если лист (`getCanReadAttribute` — нет детей).
- **Exercise** — упражнения, привязаны к главе. Тесты и эталонное решение лежат в Blade-стабах: `resources/views/exercise/solution_stub/{path}.blade.php` и `{path}_solution.blade.php`; тесты извлекаются после маркера `;;; END`.
- **ExerciseMember / ChapterMember** — join-таблицы прогресса пользователя со **state machine** (`started → finished`, переход `finish()`). За завершённое упражнение начисляется 3 балла. Конфиг графов: `config/state-machine.php` (графы `chapter_member`, `completed_exercise`), пакеты iben12/laravel-statable + sebdesign/laravel-state-machine.
- **Solution** — версии решений (scope `versioned()` — последнее на пользователя+упражнение). **Comment** — полиморфные вложенные комментарии. **Activity** — аудит через spatie/laravel-activitylog.

### Проверка решений (ключевой флоу)

- `app/Services/SolutionChecker.php` исполняет `raco test` (Racket) в шелле, оборачивая код+тесты в sandbox-шаблон, временные файлы в `storage/solutions/`. **Требует установленного Racket** — локально `raco` может отсутствовать (тогда testsuite Exercises не пройдёт).
- `app/Services/ExerciseService.php` оркестрирует `check()` (валидация → лог активности → переход в finished + баллы) и `createSolution()`.
- API: `POST /api/exercises/{id}/check`, `POST /api/exercises/{id}/solutions`, `GET /api/exercises/{id}`.

### Слои

- Контроллеры `app/Http/Controllers` сгруппированы по неймспейсам: `Api/`, `Admin/`, `Auth/`, `Settings/`, `User/`, `My/`, `Rating/`.
- Сервисы `app/Services` (ExerciseService, SolutionChecker, ChapterProgressService, ActivityService, RatingCalculator).
- DTO через spatie/laravel-data (`app/DTO`), презентеры через hemp/presenter (`app/Presenters`), политики (`app/Policies`).
- Интеграции: Socialite (GitHub/Yandex OAuth), Sentry, knplabs/github-api, spatie/laravel-sitemap.

### Фронтенд (hybrid Blade + Inertia/React)

- Inertia-корень: `resources/views/app.blade.php` (`@inertia`). React-вход: `resources/js/app.jsx`; компоненты в `resources/js/components`, страницы в `resources/js/pages`, Redux в `resources/js/slices`. Алиас `@` → `resources/js`.
- Vite (`vite.config.js`) — несколько entry points (app.jsx, editor.js, hljs.js, custom.js, app.scss).
- Локализация двухслойная: бэкенд `resources/lang/{en,ru}` + mcamara/laravel-localization (маршруты префиксованы локалью `/{locale}/...` в `routes/web.php`); фронтенд i18next (`resources/js/i18n.js`, `resources/js/locales/{en,ru}.js`).

### Тесты
- Базовые классы: `tests/TestCase.php` (RefreshDatabase, WithFaker) и `tests/ControllerTestCase.php` (создаёт авторизованного User в setUp). Фабрики в `database/factories`. Тестовое окружение — SQLite `:memory:`.

## Security

- User Racket/Scheme code is executed inside `racket/sandbox`: `sandbox-memory-limit` 256 MB, `sandbox-eval-limits` 10 s / 128 MB per evaluation, network/subprocess/file access blocked by the default security guard. The process is additionally capped by an external `timeout 20s`.
- **Known gap (#1936):** those limits apply only inside the evaluator. Student code is spliced into the wrapper module source (`solution_sandbox_wrapper.blade.php`), so input that closes the surrounding forms reaches the file top level, where no security guard applies. There is no OS-level isolation around the `racket` process.
- **Known gap (#1941):** temp solution files are named from `time()` plus the exercise id — predictable and collision-prone — and are never deleted. There is no scheduled cleanup.
- Do not add new shell execution paths without equivalent sandboxing.
- Admin routes are guarded at the route-group level (`auth` + `can:access-admin`), not only in `AdminController::__construct`. Keep it that way: a subclass that overrides the constructor without calling `parent::__construct()` silently loses the gate — that was #1938.

## Conventions

- PHP: PSR-12 + Slevomat (phpcs.xml). Строгие сравнения `===`, обязательные null-coalesce, **запрет `++`/`--`**, trailing commas в массивах, без mixed type hints. Запускать `make lint-php` / `make lint-fix`.
- JS/React: Biome (biome.json) — React 19 (без prop-types и импорта React в JSX). Blade форматируется prettier через @shufo/prettier-plugin-blade.

## Commit & PR conventions

- **Branch naming:** `feature/<issue-number>-short-description` or `fix/<issue-number>-short-description`
- **Commits:** Conventional Commits with issue reference — `feat: add solution cleanup (#42)` (the `#42` creates a GitHub link automatically)
- PR title: imperative mood, under 72 characters
- Run `make lint` and `make test` before pushing (pre-push hook enforces lint + analyse)

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4
- inertiajs/inertia-laravel (INERTIA_LARAVEL) - v2
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- laravel/socialite (SOCIALITE) - v5
- larastan/larastan (LARASTAN) - v3
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- phpunit/phpunit (PHPUNIT) - v12
- @inertiajs/react (INERTIA_REACT) - v3
- react (REACT) - v19
- eslint (ESLINT) - v10
- prettier (PRETTIER) - v3

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Follow existing application Enum naming conventions.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-react-development` when working with Inertia client-side patterns.

# Inertia v2

- Use all Inertia features from v1 and v2. Check the documentation before making changes to ensure the correct approach.
- New features: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== phpunit/core rules ===

# PHPUnit

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should cover all happy paths, failure paths, and edge cases.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files; these are core to the application.

## Running Tests

- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).

=== inertia-react/core rules ===

# Inertia + React

- IMPORTANT: Activate `inertia-react-development` when working with Inertia React client-side patterns.

=== barryvdh/laravel-debugbar rules ===

## Laravel Debugbar

Laravel Debugbar stores data from each request (queries, exceptions, views, routes, mail, etc.) for review via Artisan commands.

### Finding Requests

<code-snippet name="Find requests" lang="bash">

# List recent requests (shows summary with status, duration, memory, query count)

php artisan debugbar:find

# Filter by URI pattern (fnmatch) and/or HTTP method

php artisan debugbar:find --uri="/api/*" --method=POST

# Only show requests with issues (exceptions, slow queries, duplicates, errors)

php artisan debugbar:find --issues --max=50

# Customize issue thresholds (defaults: --min-queries=50, --min-duration=1000, --min-duplicates=2)

php artisan debugbar:find --issues --min-queries=10 --min-duration=500

# Threshold options also work standalone, filtering on just that criteria

php artisan debugbar:find --min-queries=20
</code-snippet>

`--issues` flags: exceptions, non-2xx status, high query count, slow queries, duplicate query groups, slow request duration, and failed queries. Issue filtering applies on top of the fetched result set — increase `--max` to scan further back.

### Inspecting a Request

<code-snippet name="Inspect request" lang="bash">

# Summary of all collectors (available collectors depend on config)

php artisan debugbar:get latest
php artisan debugbar:get {id}

# Full data for a specific collector

php artisan debugbar:get {id} --collector=exceptions
</code-snippet>

Use the collector name from the summary table. Common ones by issue type:
- **Error/500** → `exceptions` · **Slow page** → `queries`, `time` · **Auth** → `auth`, `gate` · **Cache** → `cache`

### Analyzing Queries

<code-snippet name="Query analysis" lang="bash">

# Overview with duplicate detection and slow query flags

php artisan debugbar:queries {id}

# Backtrace and params for a specific statement

php artisan debugbar:queries {id} --statement=N

# EXPLAIN plan or re-execute a SELECT

php artisan debugbar:queries {id} --statement=N --explain
php artisan debugbar:queries {id} --statement=N --result
</code-snippet>

Duplicate queries are a strong N+1 signal. Use `--statement=N` to get the backtrace and find the origin.

### Other Commands

- `debugbar:clear` — Clear all stored debugbar data.

</laravel-boost-guidelines>
