# AGENTS.md

`CLAUDE.md` — симлинк на этот файл. Править `AGENTS.md`.

## Project

Hexlet SICP — трекер изучения книги SICP: пользователи читают главы (иерархическое дерево), решают упражнения на Scheme/Racket, набирают баллы и попадают в лидерборды. БД — PostgreSQL везде: локально, в тестах и в проде. Фронтенд — **hybrid**: часть страниц на Blade, часть уже на Inertia + Mantine (TSX).

## Stack (версии)

**Источник истины — Docker.** Манифесты задают минимальную версию, и этот минимум держим равным образу: разошлось — подтягиваем манифест к образу, а не наоборот.

| | Версия | Где задана |
| --- | --- | --- |
| PHP | 8.5 | `Dockerfile.dev`: `PHP_VERSION=8.5`; минимум `^8.5` в `composer.json` |
| Node | 24 | `Dockerfile.dev`: `NODE_MAJOR=24`; минимум `>=24.x` в `package.json` `engines` |
| PostgreSQL | 18 | `postgres:18-alpine` в `docker-compose.yml`, `.ci.yml` и `.stage.yml` |
| Racket | не пинится | из apt базового `ubuntu:26.04` |

Ключевые пакеты: Laravel 13, PHPUnit 13, larastan 3, `inertiajs/inertia-laravel` **v3** + `@inertiajs/react` v3, React 19, Vite 8, Biome 2, spatie/laravel-data 4.

- `Dockerfile` (прод) и `Dockerfile.stage` начинаются с `INCLUDE+ Dockerfile.dev` (dockerfile-plus), поэтому версии PHP и Node задаются в одном месте — править `Dockerfile.dev`.
- CI своих версий не задаёт: `ci.yml` просто гоняет `make ci` внутри того же образа `Dockerfile.dev`.
- Подняв `php` в `composer.json`, обязательно прогнать `composer update --lock`: платформенные требования composer сверяет по `platform` в `composer.lock`, а не по `composer.json` (был #121). Команда меняет только `content-hash` и `platform`, версии пакетов не двигает.
- Racket — единственная незакреплённая зависимость, и именно она гейтит testsuite `Exercises` и `CheckControllerTest`. Апгрейд базового образа может сломать их без изменений в коде.
- `app.json` (деплой по кнопке в Heroku) живёт своей жизнью и отстал: `stack: heroku-20`, Postgres 13, `RACKET_VERSION: 7.9`. Docker-стек на него не влияет — сверяться с ним нельзя.

## Where to look

- **Issue, PR, триаж, метки** → `docs/agents/issue-tracker.md` и `docs/agents/triage-labels.md`.
- **ADR и конфликт с принятым решением** → `docs/agents/domain.md`. Глоссарий — `CONTEXT.md` в корне; доменные модели — раздел «Домен (app/Models)» ниже.
- **Allure TestOps: заливка результатов из CI, `phpunit.xml`** → `docs/agents/testing-ci.md`.
- **Фронтенд: `resources/js/**`, Inertia-страница, перенос страницы с Blade, ссылки и мутации, shared props** → `docs/agents/frontend.md`. План миграции и обоснования — `docs/frontend-migration.md` и `docs/adr/0001`–`0004`.

Скиллы активировать сразу, как зашёл в область, а не когда застрял:

- PHP, Eloquent, миграции, политики, кэш, очереди → `laravel-best-practices` (глубже — её `rules/*.md`).
- Написание и ревью тестов → `testing-best-practices`.
- `resources/js/**`, Inertia-страницы, формы, навигация → `inertia-react-development`.
- Медленная страница, N+1, исключение в запросе → `debug-using-debugbar`.
- OAuth GitHub/Yandex → `socialite-development`. Фильтры и сортировки в API → `laravel-query-builder`.

## Commands

Всё запускается через Makefile; `make-compose.mk` — то же самое под Docker, с префиксом `compose-`. Список целей смотри в самих файлах — здесь только то, чего в них не видно:

- `make cache-clear` — лечит `CSRF token mismatch`.
- `make lint` = `lint-js lint-ts types-check lint-php`. `types-check` перегенерирует `resources/js/types/generated.d.ts` из `app/DTO` и `app/Enums` и падает на диффе: поменял DTO — запусти `make generate-types` и закоммить файл. Форматирование blade не проверяется нигде: `lint-fix` его только переписывает.
- `make lint-fix` чинит PHP (phpcbf) и blade (prettier), но не JS. Автофикс Biome — отдельная цель `make lint-js-fix`.
- `make analyse` — заглушка (`@echo 'fixme'`, вызов phpstan закомментирован). Поэтому pre-push-хук и CI фактически гоняют только lint и тесты.
- Testsuite четыре — `Unit`, `Feature`, `Exercises`, `Sandbox`; по умолчанию запускается `Feature`.
- Racket (`raco`) нужен для `Exercises` и для `CheckControllerTest` в `Feature`. Локально его может не быть — тогда падают именно они, и это не регрессия: свои изменения проверять по остальным тестам.
- `make start` (без Docker) поднимает приложение через **Heroku CLI** — `heroku local -f Procfile.dev`, а не `artisan serve`. Без установленного `heroku` работает только `make start-app` / `make start-frontend` или compose-цели.
- Локальная dev-база бывает пустой (нет даже таблицы `migrations`) — тогда `php artisan migrate --seed`. `make db-prepare` делает `migrate:fresh` и сносит всё: сначала убедиться, что в базе нечего терять.
- `ViteException: Unable to locate file in Vite manifest` — не собран фронт: попросить пользователя запустить `pnpm dev` (или `make start-frontend`).
- **`php artisan route:list` в этом проекте не работает вообще:** mcamara/laravel-localization перебивает биндинг команды своим `RouteTranslationsListCommand`, а тот в `handle()` читает необъявленный аргумент `locale` → `The "locale" argument does not exist`. Флаги не помогают, `route:trans:list` не зарегистрирован. Маршруты смотреть прямо в `routes/web.php` и `routes/api.php`.

## MCP

`.mcp.json` описывает два сервера: `laravel-boost` (`php artisan boost:mcp`) и `allure-testops` (HTTP, `https://hexlet.testops.cloud/api/mcp` — тест-кейсы, тест-результаты по AQL, mute'ы). Токен `allure-testops` и проверка, что сервер работает, — `docs/agents/testing-ci.md`.

### Инструменты laravel-boost

- `search-docs` — версионная документация установленных пакетов, вызывать до правки кода. Слова склеиваются по AND со стеммингом, `"фраза"` требует точного порядка, несколько запросов в массиве работают как OR, `packages` сужает выдачу. Имя пакета в текст запроса не писать — версии сервер знает сам.
- `database-schema` — перед миграцией, `database-query` — вместо raw SQL в tinker, `browser-logs` — за ошибками фронта, `get-absolute-url` — прежде чем дать пользователю ссылку.
- `tinker --execute` — одинарные кавычки снаружи, двойные внутри: `php artisan tinker --execute 'User::where("id", 1)->count();'`.

## Architecture

### Домен (app/Models)

- **Chapter** — главы SICP в дереве (`parent_id`, `path` вида "1.1.2"). Читаема только если лист (`getCanReadAttribute` — нет детей).
- **Exercise** — упражнения, привязаны к главе. Тесты и эталонное решение лежат в Blade-стабах: `resources/views/exercise/solution_stub/{path}.blade.php` и `{path}_solution.blade.php`; тесты извлекаются после маркера `;;; END`.
- **ExerciseMember / ChapterMember** — join-таблицы прогресса пользователя со **state machine** (`started → finished`, переход `finish()`). За завершённое упражнение начисляется 3 балла. Конфиг графов: `config/state-machine.php` (графы `chapter_member`, `completed_exercise`), пакеты iben12/laravel-statable + sebdesign/laravel-state-machine.
- **Solution** — версии решений. Скоуп `versioned()` **не дедуплицирует** (#1681): `groupBy` включает `id`, а аргументы `distinct()` построитель запросов игнорирует, поэтому возвращаются все версии. Пока тикет открыт, последнюю версию брать явной сортировкой по `created_at`.
- **Comment** — полиморфные вложенные комментарии. **Activity** — аудит через spatie/laravel-activitylog.

### Проверка решений (ключевой флоу)

- `app/Services/SolutionChecker.php` пишет решение и тесты в `storage/solutions/` и запускает `racket resources/racket/sandbox.rkt <решение> <тесты>`.
- `app/Services/ExerciseService.php` оркестрирует `check()` (валидация → лог активности → переход в finished + баллы) и `createSolution()`.
- API: `POST /api/exercises/{id}/check`, `POST /api/exercises/{id}/solutions`, `GET /api/exercises/{id}`.

### Тесты

- Базовые классы: `tests/TestCase.php` (`LazilyRefreshDatabase`, `WithFaker`) и `tests/ControllerTestCase.php` (создаёт авторизованного User в setUp). Фабрики в `database/factories`. Тесты идут на соединении `pgsql_test` (`phpunit.xml`) — отдельная база PostgreSQL, переменные `TEST_DB_*`; в контейнере из `docker compose` она создаётся скриптом `database/docker/init-databases.sql`. Без запущенной базы тесты не стартуют.
- `TestCase::setUp` вызывает `withoutExceptionHandling()`. Чтобы проверять 403, 404 или редирект на логин, тест начинается с `withExceptionHandling()` — иначе вместо ответа прилетит исключение.
- Гонять минимум: `php artisan test --compact --filter=<имя>` или с путём до файла. Новый тест — `php artisan make:test --phpunit <Name>`; Pest в проекте нет, все тесты — классы PHPUnit.

## Security

- Racket/Scheme-код пользователя исполняется в `racket/sandbox`: `sandbox-memory-limit` 256 MB, `sandbox-eval-limits` 10 с / 128 MB на вычисление; сеть, подпроцессы и доступ к файлам блокирует стандартный security guard. Сверху процесс ограничен внешним `timeout 20s`.
- Лимиты и security guard действуют только внутри evaluator'а, поэтому код студента попадает туда **только как данные**: `sandbox.rkt` читает файл решения через `read` (с выключенными `read-accept-reader`/`-lang`/`-compiled`) и собирает из форм модуль песочницы. Вклеенный в исходник текст оказывается на верхнем уровне файла, вне guard'а — это был побег #1936.
- **Known gap (#1936):** изоляции уровня ОС вокруг процесса `racket` нет.
- **Known gap (#1941):** временные файлы решений именуются от `time()` и id упражнения — предсказуемо и с риском коллизий — и никогда не удаляются. Плановой очистки нет.
- Новый путь исполнения шелла проходит через тот же sandbox, что и `SolutionChecker`.
- Админские маршруты закрыты на уровне группы маршрутов (`auth` + `can:access-admin`), а не только в `AdminController::__construct`. Так и оставлять: наследник, переопределивший конструктор без вызова `parent::__construct()`, молча теряет проверку — это был #1938.

## Conventions

- PHP: PSR-12 + Slevomat (`phpcs.xml`). Строгие сравнения `===`, обязательный null-coalesce, trailing comma в массивах, явные типы вместо `mixed`. Инкремент пишется `+= 1` — `++`/`--` линтер считает ошибкой. Проверять через `make lint-php` / `make lint-fix`.
- JS/React: Biome (`biome.json`), React 19 — без prop-types и без импорта React в JSX. Blade форматируется prettier через `@shufo/prettier-plugin-blade`.
- Файлы документации создавать только по явной просьбе. Проверочные скрипты не писать — вместо них тест.

## Git, commits & PRs

- Ветки: `feature/<короткое-описание>` или `fix/<короткое-описание>`. Работа идёт по issue — номер впереди: `feature/42-solution-cleanup`.
- Коммиты: Conventional Commits — `feat: add solution cleanup`. Есть issue — номер в конце: `feat: add solution cleanup (#42)`, GitHub сам превратит его в ссылку.
- Заголовок PR: повелительное наклонение, до 72 символов.
- Перед push прогнать `make lint` и `make test`.
- **Читать файл из другой ветки — `git show <ref>:<path>`** (или `git diff <ref> -- <path>`). `git checkout <ref> -- <path>` не читает, а пишет: перетирает рабочее дерево и индекс, а с pathspec `.` — целиком.
- Перед git-командой, меняющей рабочее дерево, проверять `git branch --show-current` и `git status --short`: в длинной сессии ветка могла смениться.
- **Параллельные задачи — каждая в своём worktree:** `git worktree add .claude/worktrees/<имя> -b <ветка> upstream/main`. Общее рабочее дерево одно на все сессии: ветка в нём переключается под тобой, а в `AGENTS.md`, `composer.json` и `package.json` копятся чужие незакоммиченные правки — коммитить, перечисляя свои пути (`git add <path>…`). Новый worktree приходит без `.env`, `vendor` и `node_modules`: `cp <основное-дерево>/.env . && composer install && pnpm install` — дальше в нём работают `make lint`, `make test`, pre-push-хук, `artisan serve` и Vite.
- **Ветку, отведённую от другой ветки, перед мержем рибейзить на свежий `main`.** База уезжает в `main` сквошем, и её исходный коммит перестаёт быть предком `main`: GitHub считает дифф от общего предка и переприменяет уже существующие строки — в файле появляются дубли, при том что PR показывает `MERGEABLE` и `CLEAN`. Проверка: `git merge-base --is-ancestor <коммит-базы> upstream/main`.
