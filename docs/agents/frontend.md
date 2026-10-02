# Фронтенд: Inertia + Mantine в hybrid-периоде

Приложение переезжает с Blade на Inertia + Mantine + TypeScript постранично (**strangler**, ADR 0001). Blade- и Inertia-страницы сосуществуют, и каждое правило ниже следует из этого. План, порядок страниц и обоснования — `docs/frontend-migration.md`.

Две половины сайта — два независимых HTML-документа, переход между ними всегда полная перезагрузка:

| | Корень | Вход Vite | UI |
|---|---|---|---|
| Inertia | `resources/views/app.blade.php` | `resources/js/app.tsx` | Mantine, `@tabler/icons-react` |
| Blade | `resources/views/layouts/app.blade.php` | `bootstrap.js`, `custom.js`, `hljs.js`, `resources/sass/app.scss` | Bootstrap 5, `bootstrap-icons` |

Bootstrap и Mantine живут каждый в своём корне: reboot-стили Bootstrap ломают Mantine.

Каркас Inertia-половины: `theme.ts`, `i18n.ts`, `layouts/AppLayout.tsx` (шапка и футер), `layouts/SettingsLayout.tsx`, `layouts/AdminLayout.tsx`, `components/ui/` (`DataTable`, `Pagination`, `Filter`, `SideMenu`). Легаси `.jsx` (редактор упражнений, `components/*.jsx`) лежит вне `tsc`.

## Перенос страницы с Blade

Страница перенесена, когда все шаги выполнены и `make lint` + её тесты зелёные.

1. **Выходной DTO** в `app/DTO/<Раздел>/<Имя>PageData.php` (`spatie/laravel-data`): всё, что рендерит страница, включая готовые URL (`updateUrl`, `destroyUrl`, меню). Модель целиком в пропы не отдаётся.
2. `make generate-types` — DTO появляется в `resources/js/types/generated.d.ts` как `App.DTO.<Раздел>.<Имя>PageData`. Файл коммитится.
3. **Контроллер**: `$this->inertia($page->toArray())`. Закрытые страницы — `->withViewData(['robots' => 'noindex, nofollow'])`; `description` передаётся так же, иначе берётся общий из `layout.meta.description`.
4. **Страница** `resources/js/pages/<Раздел>/<Имя>/Index.tsx`, пропы типизированы сгенерированным DTO, обёрнута в лейаут из `layouts/`, `<Head title>` задан.
5. **Имя маршрута — в `NavigationBuilder::INERTIA_ROUTES`**: с этого момента ссылки на страницу рендерятся `<Link>`.
6. **Blade-шаблон удалён** вместе с партиалами, которые остались без пользователей.
7. **Тест** `assertInertia`: `component(...)` и пропы. В тестах включён `ensure_pages_exist`, так что опечатка в имени компонента роняет тест.
8. **Под `/ru` в браузере**: ссылки и отправка формы остаются в русской локали. Feature-тест этого не докажет: префикс локали фиксируется при регистрации маршрутов, и `route()` в тестах отдаёт URL без него.

## Правила hybrid-периода

- **URL приходят с бэкенда** — из пропов, DTO, `links[]` пагинатора. Маршруты живут под **локаль-префиксом** (`/{locale}/...`), а собранный в JS путь этот префикс теряет и молча переключает локаль сессии. Ziggy не используется (ADR 0002). Долг: склейка в `components/ControlBox.jsx`.
- **`<Link>` — на Inertia-маршрут, `<a href>` — на Blade-страницу** (в Mantine `component="a"`). `<Link>` ждёт JSON, Blade отдаёт HTML. Решает флаг `inertia` в `NavItemData`; `components/ui/NavAnchor.tsx` и `NavMenuItem` выбирают тег по нему.
- **Мутации — `router.post()` / `router.delete()`**, деструктивные — после `modals.openConfirmModal()` (пример — `pages/Settings/Account/Index.tsx`). `data-method` / `data-confirm` работают только в Blade-слое (там грузится `@rails/ujs`); на Inertia-странице такая ссылка тихо уходит GET-ом.
- **Мутация, после которой надо попасть на Blade-страницу**:
  - из `router.*` — контроллер отвечает `Inertia::location(route(...))`: Inertia-запрос получает 409 и делает полную перезагрузку, обычный — редирект;
  - из пункта меню (выход, dev-login) — `method: 'post'` в `NavItemData`, `NavAnchor` рендерит нативную форму с `csrfToken` из shared props.
- **Переводы — в `resources/lang/{en,ru}`** (ADR 0003). Фронтенд получает группы из `HandleInertiaRequests::TRANSLATION_GROUPS` в shared prop `translations`; ключ из новой группы требует добавить группу туда. Редактор упражнений живёт на своём i18next (`components/init.jsx`) — до фазы 2.
- **`window` / `document` / `localStorage` — внутри `useEffect` и обработчиков**: фаза 2 включает SSR (ADR 0004). Даты форматируются на бэкенде в DTO, респонсив — `visibleFrom` / `hiddenFrom` Mantine.

## Shared props

Задаются в `app/Http/Middleware/HandleInertiaRequests.php`, типизируются в `resources/js/types/inertia.d.ts` — новый проп правится в обоих файлах сразу. `share()` выполняется и на Blade-запросах, поэтому всё, что считается, оборачивается в замыкание.

- `nav` — `NavigationBuilder::build()`: шапка, меню пользователя, локали, футер. Blade-шапка (`layouts/_nav.blade.php`, `_footer.blade.php`) пока захардкожена отдельно — пункт меню меняется в обоих местах, пока #1980 не переведёт Blade на билдер.
- `colorScheme` — из куки `mantine-color-scheme` (исключена из `EncryptCookies`), ставится на `<html>` с сервера и в `forceColorScheme`. Переключателя в UI нет до фазы 2.
- `flash` — читает `session('success'|'error'|'warning'|'info')`, `AppLayout` показывает его уведомлением. `flash()->success()` (laracasts) пишет в другой канал, его видят только Blade-страницы.

## Проверки

- `make lint` включает `tsc`, `types-check` и `lint-frontend-rules` (grep-правила из раздела выше). Biome форматирует только `.ts`/`.tsx` (`pnpm exec biome format --write ./resources/js`); легаси `.jsx` форматтеру не следует и вне его. `make lint-js-fix` (`biome check --write`) вдобавок сортирует импорты и применяет фиксы линтера во всём `resources/js`, легаси включительно.
- Fast Refresh даёт `@viteReactRefresh` перед `@vite` — он стоит в Inertia-корне и в `exercise/show` (редактор). Blade-шаблон, который начинает грузить React-код, добавляет его так же, иначе dev падает на отсутствии преамбулы.
- Смоук curl-ом: Inertia-GET без заголовка `X-Inertia-Version` получает 409 и съедает flash — пропы удобнее читать из начального HTML обычного GET.
