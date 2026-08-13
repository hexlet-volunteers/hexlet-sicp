# AGENTS.md

`CLAUDE.md` — симлинк на этот файл. Править `AGENTS.md`.

## Project

Hexlet SICP — трекер изучения книги SICP: пользователи читают главы (иерархическое дерево), решают упражнения на Scheme/Racket, набирают баллы и попадают в лидерборды. БД — SQLite локально, PostgreSQL в проде. Фронтенд — **hybrid**: часть страниц на Blade, часть уже на Inertia + React.

## Where to look

- **Issue, триаж, метки** → `docs/agents/issue-tracker.md`. Трекер — upstream `hexlet-volunteers/hexlet-sicp` (не форк), работа через `gh` CLI, issue пишутся по-русски; пять канонических триаж-меток разобраны в `docs/agents/triage-labels.md`.
- **Домен, глоссарий, ADR** → `docs/agents/domain.md`.
- **Любая работа во фронтенде** → `docs/frontend-migration.md` и `docs/adr/0001`–`0004`.

## Commands

Всё запускается через Makefile; `make-compose.mk` — то же самое под Docker, с префиксом `compose-`. Список целей смотри в самих файлах — здесь только то, чего в них не видно:

- `make cache-clear` — лечит `CSRF token mismatch`.
- `make lint` = `lint-js lint-php`. Форматирование blade не проверяется нигде: `lint-fix` его только переписывает.
- `make lint-fix` чинит PHP (phpcbf) и blade (prettier), но не JS. Автофикс Biome — отдельная цель `make lint-js-fix`.
- `make analyse` — заглушка (`@echo 'fixme'`, вызов phpstan закомментирован). Поэтому pre-push-хук и CI фактически гоняют только lint и тесты.
- Testsuite четыре — `Unit`, `Feature`, `Exercises`, `Sandbox`; по умолчанию запускается `Feature`.
- Racket (`raco`) нужен для `Exercises` и для `CheckControllerTest` в `Feature`. Локально его может не быть — тогда падают именно они, и это не регрессия: свои изменения проверять по остальным тестам.

## Architecture

### Домен (app/Models)

- **Chapter** — главы SICP в дереве (`parent_id`, `path` вида "1.1.2"). Читаема только если лист (`getCanReadAttribute` — нет детей).
- **Exercise** — упражнения, привязаны к главе. Тесты и эталонное решение лежат в Blade-стабах: `resources/views/exercise/solution_stub/{path}.blade.php` и `{path}_solution.blade.php`; тесты извлекаются после маркера `;;; END`.
- **ExerciseMember / ChapterMember** — join-таблицы прогресса пользователя со **state machine** (`started → finished`, переход `finish()`). За завершённое упражнение начисляется 3 балла. Конфиг графов: `config/state-machine.php` (графы `chapter_member`, `completed_exercise`), пакеты iben12/laravel-statable + sebdesign/laravel-state-machine.
- **Solution** — версии решений. Скоуп `versioned()` **не дедуплицирует** (#1681): `groupBy` включает `id`, а аргументы `distinct()` построитель запросов игнорирует, поэтому возвращаются все версии. Пока тикет открыт, последнюю версию брать явной сортировкой по `created_at`.
- **Comment** — полиморфные вложенные комментарии. **Activity** — аудит через spatie/laravel-activitylog.

### Проверка решений (ключевой флоу)

- `app/Services/SolutionChecker.php` исполняет `raco test` (Racket) в шелле, оборачивая код+тесты в sandbox-шаблон; временные файлы — в `storage/solutions/`.
- `app/Services/ExerciseService.php` оркестрирует `check()` (валидация → лог активности → переход в finished + баллы) и `createSolution()`.
- API: `POST /api/exercises/{id}/check`, `POST /api/exercises/{id}/solutions`, `GET /api/exercises/{id}`.

### Слои

- DTO — spatie/laravel-data (`app/DTO`), презентеры — hemp/presenter (`app/Presenters`), права — политики (`app/Policies`).
- Внешние интеграции: Socialite (GitHub/Yandex OAuth), Sentry, knplabs/github-api, spatie/laravel-sitemap.

### Фронтенд (hybrid Blade + Inertia/React)

Приложение переезжает на Inertia + Mantine постранично (**strangler**, ADR 0001), поэтому Blade и Inertia сосуществуют — и все правила ниже следуют из этого.

Устройство:

- Inertia-корень — `resources/views/app.blade.php` (`@inertia`), вход React — `resources/js/app.jsx`. Компоненты в `resources/js/components`, Redux в `resources/js/slices`, алиас `@` → `resources/js`. Каталог `resources/js/pages` заведён под Inertia-страницы и пока содержит одну: `Settings/Profile/Index.jsx`.
- Vite (`vite.config.js`) — несколько entry points: `app.jsx`, `editor.js`, `hljs.js`, `custom.js`, `app.scss`.
- Локализация двухслойная: бэкенд — `resources/lang/{en,ru}` + mcamara/laravel-localization, фронтенд — i18next (`resources/js/i18n.js`, `resources/js/locales/{en,ru}.js`). PHP остаётся единственным источником переводов (ADR 0003).

Правила hybrid-периода:

- **URL приходят с бэкенда** — из пропов, DTO, `links[]` пагинатора. Маршруты живут под **локаль-префиксом** (`/{locale}/...` в `routes/web.php`), а собранный в JS путь этот префикс теряет и молча переключает локаль сессии. Ziggy не используется (ADR 0002). Существующий долг: склейка в `components/ControlBox.jsx`, литералы `/settings/...` в `components/Settings/SettingsLayout.jsx` и `components/Settings/ProfileForm.jsx`.
- **`<Link>` — на Inertia-маршрут, `<a href>` — на Blade-страницу** (в Mantine: `component="a"`). `<Link>` ждёт JSON с заголовком `X-Inertia`, а Blade отдаёт HTML.
- **Мутации на Inertia-странице — через `router.post()` / `router.delete()`.** `data-method` работает только в Blade-слое: `@rails/ujs` грузится из `layouts/app.blade.php`. На Inertia-странице такая ссылка тихо отработает как GET, без ошибок в консоли.
- **`window` / `document` / `localStorage` — внутри `useEffect` и обработчиков**, а не на верхнем уровне модуля: фаза 2 включает SSR (ADR 0004). Даты форматируются на бэкенде в DTO, респонсив — средствами Mantine (`visibleFrom` / `hiddenFrom`).

### Тесты

- Базовые классы: `tests/TestCase.php` (`LazilyRefreshDatabase`, `WithFaker`) и `tests/ControllerTestCase.php` (создаёт авторизованного User в setUp). Фабрики в `database/factories`. Тестовое окружение — SQLite `:memory:`.
- `TestCase::setUp` вызывает `withoutExceptionHandling()`. Чтобы проверять 403, 404 или редирект на логин, тест начинается с `withExceptionHandling()` — иначе вместо ответа прилетит исключение.
- В `phpunit.xml` стоит `stopOnFailure="true"`: прогон обрывается на первом падении, остальные ошибки не видны. Чтобы получить полную картину — временная копия конфига **в корне репозитория** (в `/tmp` не работает: пути внутри относительные).

## Security

- Racket/Scheme-код пользователя исполняется в `racket/sandbox`: `sandbox-memory-limit` 256 MB, `sandbox-eval-limits` 10 с / 128 MB на вычисление; сеть, подпроцессы и доступ к файлам блокирует стандартный security guard. Сверху процесс ограничен внешним `timeout 20s`.
- **Known gap (#1936):** эти лимиты действуют только внутри evaluator'а. Код студента вклеивается в исходник модуля-обёртки (`solution_sandbox_wrapper.blade.php`), поэтому ввод, закрывающий окружающие формы, попадает на верхний уровень файла, где security guard уже не действует. Изоляции уровня ОС вокруг процесса `racket` нет.
- **Known gap (#1941):** временные файлы решений именуются от `time()` и id упражнения — предсказуемо и с риском коллизий — и никогда не удаляются. Плановой очистки нет.
- Новый путь исполнения шелла проходит через тот же sandbox, что и `SolutionChecker`.
- Админские маршруты закрыты на уровне группы маршрутов (`auth` + `can:access-admin`), а не только в `AdminController::__construct`. Так и оставлять: наследник, переопределивший конструктор без вызова `parent::__construct()`, молча теряет проверку — это был #1938.

## Conventions

- PHP: PSR-12 + Slevomat (`phpcs.xml`). Строгие сравнения `===`, обязательный null-coalesce, trailing comma в массивах, явные типы вместо `mixed`. Инкремент пишется `+= 1` — `++`/`--` линтер считает ошибкой. Проверять через `make lint-php` / `make lint-fix`.
- JS/React: Biome (`biome.json`), React 19 — без prop-types и без импорта React в JSX. Blade форматируется prettier через `@shufo/prettier-plugin-blade`.

## Git, commits & PRs

- Ветки: `feature/<короткое-описание>` или `fix/<короткое-описание>`. Работа идёт по issue — номер впереди: `feature/42-solution-cleanup`.
- Коммиты: Conventional Commits — `feat: add solution cleanup`. Есть issue — номер в конце: `feat: add solution cleanup (#42)`, GitHub сам превратит его в ссылку.
- Заголовок PR: повелительное наклонение, до 72 символов.
- Перед push прогнать `make lint` и `make test`.
- **Читать файл из другой ветки — `git show <ref>:<path>`** (или `git diff <ref> -- <path>`). `git checkout <ref> -- <path>` не читает, а пишет: перетирает рабочее дерево и индекс, а с pathspec `.` — целиком.
- Перед git-командой, меняющей рабочее дерево, проверять `git branch --show-current` и `git status --short`: в длинной сессии ветка могла смениться.

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
