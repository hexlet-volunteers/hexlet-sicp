# Миграция фронтенда на Inertia + Mantine + TypeScript

Статус: решения приняты, реализация не начата. Дата фиксации — 2026-08-13.

Связанные ADR: [0001](adr/0001-frontend-strangler-migration.md) (strangler-миграция), [0002](adr/0002-no-ziggy-urls-from-backend.md) (URL с бэкенда), [0003](adr/0003-php-owns-translations.md) (переводы), [0004](adr/0004-ssr-deferred-seo-debt.md) (SSR и SEO-долг).

## Зачем

Сейчас в проекте два несовместимых мира: 21 Blade-страница на Bootstrap 5 и одна Inertia-страница на react-bootstrap. Данные попадают в React тремя разными способами (`view()`, `window.sicpEditorData`, Inertia props). Работают два независимых React-рантайма с двумя инстансами i18next. jQuery 4 + lodash + `@rails/ujs` грузятся на каждой Blade-странице. `@vitejs/plugin-react` установлен, но не подключён в `vite.config.js` — JSX компилит esbuild, Fast Refresh не работает. Типизации данных, приходящих с бэкенда, нет вообще.

Цель — современный вид и удобная работа с фронтом (TypeScript). Редизайн делается **внутри** миграции, отдельным проектом не выносится.

## Объём фазы 1

Двенадцать страниц. Публичные (`landing`, `home`, `pages/*`, `chapter/*`, `exercise/*`, `user/*`, `rating/*`, `auth/*`) остаются на Blade до фазы 2, потому что им нужен SSR — см. [ADR 0004](adr/0004-ssr-deferred-seo-debt.md).

```
settings/profile      (уже Inertia, переписывается на Mantine)
settings/account
admin/users, admin/users/edit, admin/comments, admin/solutions, admin/export
my/index, my/solutions
log/index, comment/index
solution/index, solution/show
```

Три из них — `log/index`, `comment/index`, `solution/index` — публичны и индексируются. Это принятый SEO-долг, зафиксирован в [ADR 0004](adr/0004-ssr-deferred-seo-debt.md).

## Конвенции и почему именно так

### URL никогда не склеиваются в JS

Все URL приходят с бэкенда: в пропах страницы, внутри DTO, в `links[]` пагинатора. Ziggy не используется — см. [ADR 0002](adr/0002-no-ziggy-urls-from-backend.md).

В коде уже есть четыре живых бага этого класса, все чинятся по ходу миграции: `resources/js/components/ControlBox.jsx:63`, `resources/js/components/Settings/ProfileForm.jsx:18`, `resources/js/components/Settings/SettingsLayout.jsx:23,31`. Под `/ru` эти ссылки уходят на en-версию и молча меняют локаль сессии.

### Inertia `<Link>` только на уже переехавшие маршруты

`<Link>` ждёт JSON с заголовком `X-Inertia`; Blade-страница отдаёт HTML, и Inertia либо делает жёсткий `window.location`, либо (в dev) показывает модалку с исходником страницы.

**На ещё-Blade-страницу — обычный `<a href>`** (в Mantine: `component="a"`).

Чтобы это не решалось по памяти в каждом компоненте, список переехавших маршрутов живёт в одном месте — `App\Support\Navigation\InertiaRoutes::has(string $routeName)`, а DTO ссылок несут вычисленный флаг `inertia: bool`.

Ссылки из страниц фазы 1, ведущие в Blade-территорию: `users.show` (встречается в 8 страницах), `exercises.show`, `chapters.show`, `comments.show`, `password.request`, все элементы шапки и футера кроме перенесённых.

### `data-method` на Inertia-странице не работает

`ujs.start()` вызывается в `resources/js/bootstrap.js:13`, который подключён только из `resources/views/layouts/app.blade.php`. На Inertia-страницах `@rails/ujs` нет, поэтому любая ссылка с `data-method` отработает как обычный GET **без ошибок в консоли** — тихий баг.

- `logout` в Mantine-шапке — `router.post(logoutUrl)`, не `<Link>` и не `<a>`. Для этого в `NavItemData` есть поле `method`
- деструктивные действия — `modals.openConfirmModal()` + `router.delete()`, а не `data-confirm`
- оставшиеся `data-method` в Blade-территории (`chapter/show.blade.php:67-68`, `components/comment/_comment.blade.php:47`) не трогаем до фазы 2

### Никаких `window`/`document` на верхнем уровне модуля

Фаза 2 включает SSR, и код фазы 1 должен быть к нему готов. Правил Biome под это нет, компенсация — этот документ и `CLAUDE.md`.

Уже нарушено в двух местах, оба чинятся в первом PR: `resources/js/i18n.js:13` (`document.documentElement.lang`) и `resources/js/app.jsx:7` (`document.getElementsByTagName('title')`).

Сопутствующие правила:
- респонсив только средствами Mantine (`visibleFrom`/`hiddenFrom`, CSS-based). `useMediaQuery` в нашем коде даст hydration mismatch
- даты форматируются **на бэкенде, внутри DTO**. `toLocaleString()` на клиенте даст на сервере и в браузере разный текст. Сейчас формат разный: `d.m.Y H:i` в админке и голый `{{ $created_at }}` в `log`/`comment`/`solution` — унифицируется при переносе

### Типы пропов генерируются, не пишутся руками

`spatie/laravel-typescript-transformer` читает `app/DTO/**` и пишет `resources/js/types/generated.d.ts` (глобальный `declare namespace App.DTO`). Файл **коммитится** — иначе `tsc` не проходит на чистом клоне без PHP-окружения, и фронтендер не может работать без бэкенда.

После изменения DTO — `make generate-types`. В `make lint` есть `make types-check`: перегенерировать и `git diff --exit-code`, чтобы сгенерированный файл не разъезжался с DTO.

Существующие 9 DTO — **входные** объекты (валидация форм). Для пропов нужны новые, выходные: ~15 за фазу 1 плюс общие (`PaginationData`, `PaginationLinkData`, `NavItemData`, `NavigationData`, `AuthUserData`, `FlashData`, `LocaleLinkData`).

Осторожно с `app/DTO/Progress/ChapterProgressData.php` — он держит внутри модель `Chapter` и `Illuminate\Support\Collection`, из этого transformer сделает `any`.

### Переводы: PHP владеет, i18next потребляет

См. [ADR 0003](adr/0003-php-owns-translations.md). `App\Support\Inertia\TranslationBag` отдаёт группы для текущей локали в shared props, i18next грузит их как resources.

Дополнительно из shared props приходит `scope` — префикс ключей текущей страницы, а `useTView()` в `resources/js/lib/scope.ts` позволяет писать относительные ключи: `tView('.title')` → `t('admin.users.title')`.

**`scope` задаётся явно, а не выводится из имени контроллера.** Автовывод не сходится с деревом словарей на большинстве страниц:

| Контроллер@экшен | Автовывод дал бы | Фактический префикс ключей |
|---|---|---|
| `ActivityController@index` | `activity.index` | `activitylog` |
| `SolutionController@index` | `solution.index` | `views.solution.index` |
| `MyController@show` | `my.show` | `progresses` + `layout.nav` |
| `Settings\AccountController@index` | `settings.account.index` | `account` + `settings.account` |
| `Admin\UserController@index` | `admin.users.index` | `admin.users` ✔ |

Поэтому `Controller::inertia()` получает третий параметр: `inertia(array $props = [], ?string $view = null, ?string $scope = null)`. Приведение legacy-словарей к конвенции (`activitylog` → `activity`, `views.solution.index` → `solution.index`) — задача фазы 2, не фазы 1.

**Про «один инстанс i18next».** Полностью схлопнуть два инстанса в фазе 1 нельзя: второй живёт на Blade-странице `exercise/show`, куда shared props не доходят, и работает на ~20 ключах, которых в PHP-словарях нет (`run`, `editor`, `output`, `tests`, `editorContent.*`, `tooltip.*`). В фазе 1 получаем **один модуль-фабрику с одним конфигом и две точки инициализации**. Литерально один инстанс появится в фазе 2 вместе с переносом ключей редактора в `resources/lang/{ru,en}/editor.php`.

### Навигация: одно дерево, два рендерера

Всю фазу 1 шапка и футер нужны в двух видах — Bootstrap для Blade-страниц и Mantine для Inertia-страниц. Чтобы они не разъехались, состав меню считает один класс `App\Support\Navigation\NavigationBuilder`; Blade получает его через view composer, Mantine — через shared prop `nav`. Разъехаться может только внешний вид, не состав.

Расхождения, которые уже есть и схлопываются этим классом:
- `layouts/_nav.blade.php:38-63` (админ-дропдаун, 4 пункта) vs `admin/partials/navigation.blade.php` (4 пункта) vs `admin/users/edit.blade.php:10-23` (**3 пункта**, Export потерян)
- `settings/_menu.blade.php` vs `components/Settings/SettingsLayout.jsx:22-37` — вторая реализация считает активный пункт через `url?.includes('profile')`, первая через `request()->routeIs()`

`active` считается только через `request()->routeIs(...)`. Не потерять условную ветку `App::isLocale('ru')` из `_nav.blade.php:26-31`: для `ru` ссылка «Как изучать» ведёт на внешний `guides.hexlet.io`, для `en` — на внутренний `pages.show`.

### Пагинация: собственный конверт

`spatie/laravel-data` умеет `PaginatedDataCollection`, но её `toArray()` отдаёт `{data, links: {first,last,prev,next}, meta}` — и это **не** `links[]` Laravel-пагинатора `[{url, label, active}]`. Два разных `links` под одним именем гарантируют путаницу, поэтому у нас свои `App\DTO\PaginationData` и `PaginationLinkData` со статическим `fromPaginator()`.

Форма пропа списковой страницы: `{ items: XData[], pagination: PaginationData }`.

**`->withQueryString()` в контроллере, всегда** — иначе вторая страница теряет фильтр. Сейчас `Admin\{User,Comment,Solution}Controller` делают `->appends($request->query())`; `ActivityController`, `CommentController`, `My\SolutionController` — голый `paginate()`; `SolutionController` вызывает `withQueryString()` **во вьюхе** (`solution/index.blade.php:72`).

Все 7 `paginate()` и все 7 `->links()` в проекте — внутри фазы 1. Значит `Paginator::useBootstrap()` (`AppServiceProvider.php:61`) становится мёртвым ровно по её завершении и удаляется последним PR. **Раньше не трогать** — оставшиеся Blade-пагинаторы уедут на Tailwind-дефолт.

### Таблицы и фильтры

Свой тонкий слой на Mantine `Table`, без `mantine-datatable`: таблицы в проекте простые (максимум 6 колонок, ни виртуализации, ни inline-edit, ни группировок).

Сортировки нет и не добавляем: `allowedSorts` — 0 вхождений во всём `app/`. `resources/views/components/sorting_widget.blade.php` — мёртвый код, написанный под конвенцию `?sort=-column`, которая нигде не реализована; удаляется в уборочном PR.

Фильтры: `spatie/laravel-query-builder ^7.0` уже стоит, и имена полей (`filter[name]`, `filter[email]`, `filter[user.name]`, `filter[exercise_id]`) уже совпадают с его контрактом. `Filter.tsx` — Inertia `useForm` + `form.get(action, { preserveState: true, replace: true })`; кнопка сброса — `<Link href={action}>`.

### Формы: Inertia `useForm`, без `@mantine/form`

Единственный источник истины состояния формы — Inertia. `@mantine/form` дал бы второе состояние и ручную синхронизацию ошибок.

`<Field name="...">` по одному `name` выводит:
- `label` — через `useTView()`, с возможностью override
- `error` — из `usePage().props.errors[name]` по dot-path. Laravel складывает ошибки именно так, и `spatie/laravel-data` даёт те же ключи
- `required` — по пропу

Исключение: **`admin/export`**. `ExportController@store` возвращает `BinaryFileResponse` (скачивание файла), а `useForm().post()` ждёт Inertia-ответ или редирект. Там нужна нативная `<form method="POST">` со скрытым `_token` из shared-пропа `csrfToken` (Mantine `Select` в такой форме не годится — он не рендерит нативный `<select>`).

### Тёмная тема без `ColorSchemeScript`

Схема хранится в куке `mantine-color-scheme`, читается в `HandleInertiaRequests`, уезжает пропом `colorScheme` и ставится атрибутом `data-mantine-color-scheme` на `<html>` **с сервера**. `ColorSchemeScript` из документации Mantine не используем: нет inline-скрипта (совместимо с будущим CSP), нет FOUC, работает при SSR.

Инфраструктура ставится в фазе 1, **переключателя в UI нет до фазы 2**: `resources/views/layouts/app.blade.php:2` жёстко `data-bs-theme="light"`, при этом `_custom.scss:25` и `_activity_chart.scss:12-18` уже содержат `[data-bs-theme='dark']`-правила — Bootstrap-тёмная тема наполовину написана и выключена. Включать переключатель, пока живы две схемы, значит синхронизировать их через одну куку ради месяцев переходного периода.

### Изоляция бандлов

`resources/views/app.blade.php` (Inertia-корень) и `resources/views/layouts/app.blade.php` (Blade-лейаут) — два независимых HTML-документа, и переход между ними всегда полная перезагрузка. Поэтому конфликта каскада между Bootstrap и Mantine нет, **при условии что они не встречаются в одном документе**:

- Bootstrap (`resources/sass/app.scss`) грузится только из Blade-лейаута
- Mantine CSS импортируется из `resources/js/app.tsx` (Vite сам эмитит CSS-чанк для входа), отдельный Vite-вход для него не нужен и вреден
- иконки: `bootstrap-icons` с CDN только в Blade-половине, Mantine-страницы на `@tabler/icons-react`. Иконки будут отличаться визуально — осознанная плата

Сейчас `app.blade.php:11` грузит `@vite(['resources/js/app.jsx', 'resources/sass/app.scss'])`, и убрать оттуда `app.scss` можно только тем же коммитом, которым `Settings/Profile` переписывается с react-bootstrap на Mantine.

### Флеш-сообщения: сейчас два канала, оба частично сломаны

Это факт, а не риск:

- `resources/views/vendor/flash/message.blade.php:1` читает `session('flash_notification')` и в конце его очищает
- `HandleInertiaRequests` читает `session('success'|'error'|'warning'|'info')` — **другой канал**
- `Settings/AccountController.php:29,32` и `CommentController` пишут через `flash()->success()` → в `flash_notification` → Inertia-страница их **не увидит**
- `Admin/UserController.php:44` пишет `->with('success', 'User updated')` → Blade-лейаут этого канала **не читает**, сообщение не показывается никогда

Переходный период это усугубляет: если Blade-экшен редиректит на Inertia-страницу с флешем в `flash_notification`, сообщение теряется и остаётся в сессии до следующей Blade-страницы.

Решение — один `App\Support\Inertia\FlashBag`, читающий **оба** канала, нормализующий уровень (`error`/`danger` → `red`) и делающий `session()->forget()` того, что отдал. На фронте — `@mantine/notifications`. `laracasts/flash` и `resources/views/vendor/flash/` живут до конца фазы 2.

### Шапка в фазе 1 повторяет текущую

Редизайн внутри миграции и постраничный переход совместимы для **содержимого** страниц, но не для **шелла**: пользователь, идущий с `/chapters` (Bootstrap-шапка) на `/log` (Mantine-шапка), увидит смену внешнего вида шапки, и это будет выглядеть как баг.

Поэтому шапка и футер в фазе 1 делаются максимально похожими на текущие Bootstrap-версии — тот же логотип, порядок пунктов, плотность, цвета. Редизайн шелла — первый PR фазы 2, когда Bootstrap-половина умирает; там же появляется переключатель темы.

### Шрифт

`resources/sass/_variables.scss:2` объявляет `$font-family-sans-serif: 'Onest', sans-serif`, но ни `@font-face`, ни `<link>` на шрифт в проекте нет — сайт рендерится системным sans-serif, объявление декоративное. В Mantine-теме шрифт надо выбрать осознанно: либо реально подключить Onest (self-host в `public/fonts`), либо честный системный стек.

## Этап 0 — фундамент

Ни одна страница не меняется визуально.

### Зависимости

- **npm dependencies:** `@mantine/core`, `@mantine/hooks`, `@mantine/notifications`, `@mantine/modals`, `@tabler/icons-react`
- **npm devDependencies:** `typescript`, `@types/react`, `@types/react-dom`, `postcss`, `postcss-preset-mantine`, `postcss-simple-vars`
- **composer require-dev:** `spatie/laravel-typescript-transformer`. Именно dev: атрибут `#[TypeScript]` нужен только артизан-команде, прод-сборка с `--no-dev` не ломается
- **Не добавлять:** `mantine-datatable`, `@mantine/form`, `@mantine/code-highlight` (в 8+ тянет `shiki` с WASM и асинхронной инициализацией — враждебен SSR; `highlight.js` уже в зависимостях, `<CodeBlock>` делается на `hljs.highlight()` как чистая функция)
- Перед пиннингом проверить `npm view @mantine/core version`. Если 9.x ещё нет — 8.x, план не меняется
- `.github/dependabot.yml`: в группе `ui` заменить `@mui/*`, `tailwind*`, `shadcn*` на `@mantine/*`, `@tabler/*` — иначе Mantine попадёт в группу `other-js` с 30 пакетами

### Конфиги

| Файл | Что |
|---|---|
| `tsconfig.json` | новый. `strict`, `noEmit`, `jsx: react-jsx`, `moduleResolution: bundler`, `paths: {"@/*": ["resources/js/*"]}`. `include` только `resources/js/**/*.{ts,tsx}` и `types/**/*.d.ts`; `allowJs: false` — легаси `.jsx` сознательно вне проверки |
| `jsconfig.json` | **удалить** — второй источник истины по алиасам |
| `postcss.config.cjs` | новый. `postcss-preset-mantine` + `postcss-simple-vars` с брейкпоинтами |
| `vite.config.js` | подключить `@vitejs/plugin-react()` (стоит в devDeps, но не в `plugins`); **убрать блок `esbuild: { jsx, jsxImportSource }`** — теперь это делает плагин, заодно появляется Fast Refresh; вход `app.jsx` → `app.tsx`; остальные 4 входа и `app.scss` без изменений |
| `config/typescript-transformer.php` | новый. `auto_discover_types: [app_path('DTO')]`, `DataTypeScriptTransformer` + `EnumTransformer`, `writer: TypeDefinitionWriter`, `output_file: resources/js/types/generated.d.ts` |
| `biome.json` | исключить `resources/js/types/generated.d.ts` |
| `Makefile` | `lint-ts: npm run types` (`tsc --noEmit`); `lint: lint-js lint-ts lint-php`; `generate-types: php artisan typescript:transform --format`; `types-check: generate-types` + `git diff --exit-code` на сгенерированный файл, добавить в `lint`; в `setup` добавить `generate-types` перед `npm run build` |

`docker-compose.ci.yml` уже гоняет `make ... lint`, так что `tsc` попадает в CI автоматически. `pre-push` вызывает `make pre-push-hook` → `lint analyse`, править хук не надо.

Отдельно стоит знать: `make analyse` сейчас заглушен (`@echo 'fixme'`, phpstan закомментирован), статического анализа PHP в проекте нет. После этого этапа `tsc` станет единственным работающим статическим анализатором.

### Структура `resources/js/`

```
app.tsx            новый вход. import.meta.glob('./pages/**/*.tsx') БЕЗ eager
                   (сейчас app.jsx:13 использует eager — все страницы падают в один чанк)
theme.ts           createTheme: primary, defaultRadius, fontFamily, дефолты Table/Button/Card
i18n.ts            фабрика createI18n({ lng, resources }) вместо модуля с сайд-эффектом
types/
  generated.d.ts   генерируется из app/DTO, коммитится
  inertia.d.ts     module augmentation @inertiajs/core -> типизированный usePage().props
layouts/           AppLayout (Mantine AppShell), AdminLayout, SettingsLayout
components/ui/     DataTable, Pagination, Filter, Field, CodeBlock
lib/               scope.ts (useTView), format.ts

не трогаем до фазы 2:
  bootstrap.js custom.js hljs.js editor.js
  components/*.jsx slices/*.js common/* context/* locales/*.js
```

### Shared props

`app/Http/Middleware/HandleInertiaRequests.php` сейчас отдаёт `auth.user`, `locale`, `flash`. Добавляется остальное.

**Тяжёлые пропы — через замыкания.** `share()` вызывается на каждом запросе web-группы, то есть и на всех 21 Blade-странице; замыкания резолвятся только при построении Inertia-ответа.

| Проп | Источник |
|---|---|
| `auth.user` | новый `App\DTO\AuthUserData` — сейчас в JSON летит вся модель |
| `locale`, `locales` | `LaravelLocalization` + готовые URL из `getLocalizedURL()` для переключателя |
| `translations` | `App\Support\Inertia\TranslationBag` (замыкание) |
| `scope` | из имени экшена, с явным override |
| `nav` | `App\Support\Navigation\NavigationBuilder` (замыкание) |
| `colorScheme` | кука `mantine-color-scheme` |
| `csrfToken` | для нативных `<form>` — нужен `admin/export` |
| `flash` | `App\Support\Inertia\FlashBag` |

### Группы переводов и три дырки в словарях

Группы для фазы 1: `layout`, `account`, `settings`, `my`, `progresses`, `activitylog`, `comment`, `admin`, `views`, `solution`, `pagination`, `validation`. **Никогда не включать** `sicp`, `exercise`, `exercises/**`.

Чинится тем же PR:
- `resources/lang/ru/pagination.php` **отсутствует** (есть только в `en`)
- нет ключа «Загрузка…» — `components/Settings/ProfileForm.jsx:68` берёт `t('loading')` из JS-словаря. Добавить `layout.common.loading` в оба языка
- `t('layout.save')` там же — в PHP это `layout.common.save`

### Inertia-корень

`resources/views/app.blade.php` (16 строк) научить принимать `description`, `robots`, `hreflang` (`<x-hreflang-tags />` уже существует как компонент), `data-mantine-color-scheme` и осмысленный `<title>`. Сейчас там нет ничего из этого, `<title>` жёстко `config('app.name')` (локально — `"SICP Local"`). Без этого потеря description и hreflang расползётся по всем перенесённым страницам.

### Первый view composer в проекте

`app/Providers/ViewServiceProvider.php` — новый. `View::composer('layouts._nav', ...)` и то же для `_footer`. Регистрация в `config/app.php` в блоке `App\Providers\*` (`bootstrap/providers.php` в проекте отсутствует). View composer'ов сейчас 0.

Переписывание `_nav.blade.php` и `_footer.blade.php` на итерацию по дереву должно быть **поведенчески нейтральным**: та же Bootstrap-разметка, тот же HTML на выходе.

## Порядок PR-ов

Логика: фундамент без изменения UI → единственная существующая Inertia-страница на Mantine → самая показательная таблица как пилот → деструктивные действия → однотипные админ-таблицы пачками → формы → тяжёлое дерево.

**PR 1 — фундамент.** Всё из этапа 0. `app.scss` из `app.blade.php` пока не убираем. Тесты: существующие зелёные + тест `NavigationBuilder` на трёх ролях (гость/юзер/админ) + `assertInertia` на `settings.profile.index` с проверкой shared-пропов `translations`, `scope`, `nav`.

**PR 2 — Inertia-корень на Mantine.** `app.blade.php` на `app.tsx`, `app.scss` убран. `AppLayout.tsx`, `SettingsLayout.tsx`, `Field.tsx`. `pages/Settings/Profile/Index.tsx` на Mantine; удаляются `Index.jsx`, `components/Settings/*.jsx`, `components/Common/Flash.jsx`, `resources/js/i18n.js`, `resources/js/app.jsx`, мёртвый `resources/views/settings/profile/index.blade.php`. Починка трёх склеенных URL. `ProfileController@index` отдаёт `updateUrl` в пропах.

**PR 3 — пилот: `log/index`.** Здесь появляются `PaginationData`, `DataTable.tsx`, `Pagination.tsx`. Страница выбрана пилотом не потому, что она простая, а наоборот: `@switch` на 6 ветвей с `trans_choice`, `ChapterHelper`, двумя видами ссылок и N+1 — ровно тот случай, который заставляет обкатать «бэкенд считает текст и URL, React только рендерит». Логика из `log/index.blade.php:28-74` уезжает в маппер в `App\DTO\Activity\ActivityItemData`. Попутно фикс N+1: `log/index.blade.php:68` вызывает `Exercise::findByPath()` внутри цикла по 15 записям.

**PR 4 — `settings/account`.** Паттерн деструктивных действий: `modals.openConfirmModal()` + `router.delete()` вместо `data-method="delete" data-confirm`. Ссылка на `route('password.request')` — обычный `<a>`, страница ещё Blade.

**PR 5 — `admin/users` (index).** Здесь появляются `AdminLayout.tsx` и `Filter.tsx`. `App\DTO\Admin\UserListItemData` с `showUrl`/`editUrl`. Тест: заменить `assertViewIs`/`assertViewHas` (`Admin/UserControllerTest.php:28-29`) на `assertInertia` плюс новый тест на фильтр `?filter[name]=…` — фильтры сейчас не покрыты вообще.

**PR 6 — `admin/comments` + `admin/solutions`.** Тот же паттерн ×2. `contentHtml` безопасен для `dangerouslySetInnerHTML`: `MarkdownHelper::text()` использует `Parsedown::setSafeMode(true)`, то есть не хуже текущего Blade `{!! !!}`. Удаляются `admin/partials/{search-form,navigation}.blade.php`.

**PR 7 — `admin/users/edit` + `admin/export`.** Уходит `spatie/laravel-html` (10 вызовов) и продублированное руками админ-меню без пункта Export. `admin/export` — нативная форма (см. раздел про формы). Попутно: `Admin/UserController.php:44` кладёт нелокализованную `'User updated'` → `__('layout.flash.success')`, и `Admin/UserControllerTest.php:70` ассертит эту строку, тест придётся править.

**PR 8 — `my/solutions`.** Простая таблица. Попутно исправляется битая разметка: `my/solutions.blade.php:31-33` — незакрытый `<a>`, `<tr>` с данными лежат внутри `<thead>`. **До этого PR** сделать страховку Playwright (см. ниже) — это первая страница, покрытая спеками.

**PR 9 — `my/index`.** Самая тяжёлая страница фазы 1. Рекурсивный `App\DTO\Progress\ChapterNodeData`; `ChapterProgressData` не переиспользуем (держит модель и `Collection`, в TS выродится в `any`). `ChapterProgressService` — написан и нигде не используется — остаётся вычислителем, маппер превращает его результат в DTO; заодно оживает мёртвый сервис. Bootstrap-табы → Mantine `Tabs orientation="vertical"`, рекурсивный `partials/chapter_partial.blade.php` → рекурсивный `<ChapterNode>`. Перед удалением партиала проверить, что `user/show` использует другой файл (`partials/user_chapter_partial.blade.php`).

**PR 10 — `solution/index`.** `<Filter>` с Mantine `Select searchable`. Внимание на объём: `SolutionController.php:32-34` грузит все ~356 упражнений и делает `pluck('fullTitle','id')` — ~15 КБ в пропах на каждый рендер; терпимо, при желании `Inertia::optional`. Фиксы: `$filter['name']` (строка 20) не используется, вьюха читает `user.name`; `$solutionAuthors` (35-38) передаётся во view и не используется. `->withQueryString()` переезжает из вьюхи в контроллер. Табы `exercise/navigation.blade.php` воспроизводятся в Mantine, но **сам партиал не удаляется** — он ещё нужен `exercise/index`.

**PR 11 — `solution/show` (два контроллера, только вместе).** `SolutionController@show` и `User\SolutionController@show` рендерят одну вьюху; переводятся одновременно, с **явным** `$view = 'Solution/Show'` — автовывод дал бы `Solution/Show` и `User/Solution/Show`, две копии одной страницы. Второй параметр `Controller::inertia()` существует ровно для этого. Bootstrap pills → Mantine `Tabs`, `@solution` → `<CodeBlock>`. Сохранить `noindex, nofollow`. Попутно чинится дублирование `id="pills-tab"` в двухпанельном режиме (`solution/show.blade.php:27,64`) — невалидный HTML.

**PR 12 — `comment/index` + уборка.** Удаляются: `components/sorting_widget.blade.php`; `components/bs/form/email.blade.php` (не используется никем), в `checkbox.blade.php:4` закрывается `<div>`; `Blade::include('components.solutions', ...)` в `AppServiceProvider.php:67` (указывает на несуществующую вьюху); регистрация `@hreflang_tags` в `AppServiceProvider.php:69` (используется компонент `<x-hreflang-tags />`, но саму вьюху и `app/View/Components/HreflangTags.php` **оставить**); `Paginator::useBootstrap()` в `AppServiceProvider.php:61`. Плюс фикс `ControlBox.jsx:63`.

## Тесты

`assertInertia` (`AssertableInertia`) добавляется на каждую перенесённую страницу сразу. Сейчас таких ассертов 0.

Привязки к Blade в PHPUnit-тестах немного: 3 `assertViewIs` + 3 `assertViewHas` в `Admin/{User,Comment,Solution}ControllerTest` и 2 `assertDontSee` в `HomeControllerTest`. `assertSee` — 0 вхождений, HTML-разметку не проверяет ни один тест.

### Playwright: страховка нужна до PR 8

13 спеков в `tests/playwright/sicp_playwright/tests/{book,profile,auth}` — единственная защита от регрессий в разметке. Селекторы построены на ARIA-ролях и видимом русском тексте, `data-testid` — 0 вхождений; к смене CSS-классов они устойчивы, но ломаются от смены **роли** элемента (`<a data-method="post">` → `<button>` превращает `getByRole('link')` в `getByRole('button')`).

Проблема в другом: `baseURL` в `playwright.config.js` закомментирован, тесты ходят на прод `https://sicp.hexlet.io/ru` с реальными кредами `test@test.com`/`12345678`, а `profile/regist_del_user.spec.js` регистрирует и удаляет аккаунт **на проде**. То есть эти тесты не увидят изменения до деплоя, и «починим, когда сломается» здесь значит «сломается в проде».

Страховка на один PR: `baseURL` как `process.env.PLAYWRIGHT_BASE_URL ?? 'https://sicp.hexlet.io'`, абсолютные `page.goto()` → относительные пути. После этого спеки гоняются против стенда (`docker-compose.stage.yml` уже есть) перед мержем каждого PR.

Фаза 1 задевает 4 спека из 13: `profile/my_progress`, `profile/my_solutions`, `profile/regist_del_user`, `book/code-review`. Под наибольшим риском `my_progress` (PR 9) — там `getByRole('tab')` и специфика `nav-pills`.

## Проверка

Локально:

1. `make setup` → `make generate-types` → `make lint` (включает `lint-ts`) → `make test`
2. `make start`, пройти руками все перенесённые страницы в **обеих локалях** (`/log` и `/ru/log`) — проверка, что ни один URL не склеен в JS
3. Переходы туда-обратно между половинами: `/chapters` → `/log` → `/ru/my` → `/exercises` — навигация не ломается и не мигает
4. Флеш в обе стороны: `Admin\UserController@update` → редирект на Inertia-страницу (сообщение видно); `AccountController@destroy` → редирект на Blade `home` (сообщение видно)
5. Тёмная тема: выставить куку `mantine-color-scheme=dark` руками, перезагрузить — нет вспышки светлой темы
6. `docker compose -f docker-compose.ci.yml up` — то, что реально гоняет CI

Автоматически:

- `assertInertia` на каждой перенесённой странице; `assertViewIs`/`assertViewHas` в `tests/Feature/Http/Controllers/Admin/**` исчезли
- `make types-check` — `generated.d.ts` соответствует `app/DTO/**`
- `grep -rn 'href="/' resources/js/pages resources/js/layouts` — пусто
- `grep -rn 'window\.\|document\.' resources/js/{pages,layouts,components/ui,lib}` — только внутри обработчиков и эффектов

## Критерии готовности фазы 1

1. Все 12 страниц отдают `Inertia\Response`; соответствующих Blade-файлов в репозитории нет
2. `resources/js/pages/**` — только `.tsx`; `tsc --noEmit` зелёный; `make lint` включает `lint-ts` и `types-check`
3. Ни один проп страницы не содержит склеенного в JS URL
4. Дерево навигации строится одним классом; в `_nav.blade.php` и `_footer.blade.php` нет захардкоженных пунктов; `admin/users/edit.blade.php` удалён вместе со своей копией меню
5. Флеш работает при любых комбинациях редиректов Blade ↔ Inertia
6. `resources/sass/app.scss` не подключается из `resources/views/app.blade.php`; Mantine CSS не подключается из `layouts/app.blade.php`
7. Все 7 пагинаторов отдают `PaginationData`; `Paginator::useBootstrap()` удалён
8. Тёмная тема: кука и серверный `data-mantine-color-scheme` работают, переключателя в UI нет
9. Мёртвый код из PR 12 удалён
10. `CLAUDE.md` описывает конвенции фронтенда и ссылается на этот документ

## Что решить на входе в фазу 2

1. **Где живёт SSR-сайдкар.** См. [ADR 0004](adr/0004-ssr-deferred-seo-debt.md). Heroku-dyno 512 МБ, Node нужно 300–500 МБ. Варианты: второй dyno (платный, и `inertia-laravel` обращается к нему по сети, а не по localhost); уход на Render/Fly (`Dockerfile`, `docker-compose.stage.yml`, `make docker-build-render` уже есть); один контейнер с двумя процессами (нужен Docker вместо билдпаков, а `app.json` использует три, включая racket для проверки решений)
2. **Судьба 356 `exercise/listing/**` и 266 `solution_stub/**`** — 2463 из 2738 вызовов `__()` в проекте. Это контент, а не UI. Варианты: оставить Blade-рендер и отдавать HTML пропом; конвертировать в Markdown/MDX; оставить `exercise/show` на Blade
3. **Судьба редактора упражнений** (Redux с 5 слайсами, CodeMirror 6, свой i18next, react-bootstrap) и перенос его ~20 ключей в `resources/lang/{ru,en}/editor.php`
4. **Момент смерти `bootstrap.js`, `custom.js`, jQuery 4, lodash** — привязан к `exercise/show` (хеш-навигация по табам в `custom.js`) и к последним `data-method` в `chapter/show` и `components/comment/_comment.blade.php`
5. **Редизайн шелла** и переключатель тёмной темы — первый PR фазы 2
6. **`hljs.js` + `highlight.js/styles/github.css`** уходят вместе с Blade-страницами; `<CodeBlock>` становится единственным подсветчиком
7. **Приведение legacy-словарей к `scope`-конвенции** — если хотим избавиться от ручного override
