# Cooldown (minimum release age) для Composer-зависимостей

Дата исследования: 2026-08-25. Проверялось на Composer 2.10.2 (2026-07-01), установленном локально.

## Короткий вывод

1. **Встроенной опции cooldown в Composer нет** ни в 2.10.2, ни раньше. Но она уже спроектирована, имя ключа зарезервировано прямо в коде 2.10, а тикет `composer/composer#12633` висит с milestone **2.11**. То есть ждать осталось один минор.
2. **`composer audit` и `roave/security-advisories` задачу не закрывают вообще.** Они блокируют версии с *известными* CVE, а cooldown нужен ровно для тех дней, пока про закладку ещё никто не знает.
3. **Private Packagist задержки публикации не даёт** — только ручное одобрение *новых пакетов* (не версий) при mirroring.
4. **Писать плагин можно** (`PluginEvents::PRE_POOL_CREATE` + `setPackages()` — ровно тот хук, куда 2.11 и вклеит фичу), но это ~100 строк ради ожидания одного минора.
5. **Дата релиза уже есть офлайн — в `composer.lock` есть поле `time` у каждого пакета** (203 штуки в нашем локе). Плюс `composer show --latest --format=json` отдаёт `release-date` и `latest-release-date` для установленных *и* доступных версий, включая транзитивные.

**Что делать (ленивый вариант):** одна цель в Makefile поверх `composer show --latest --format=json`, ~15 строк, ноль зависимостей, ноль обращений к API. Детали — в конце. Плагин и прокси — не писать, дождаться Composer 2.11.

---

## 1. Есть ли в Composer встроенный minimum release age

**Нет — но зарезервирован.** Источники первичные.

### 1.1. Что реально лежит в Composer 2.10.2

В phar'е установленного Composer есть новый неймспейс `Composer\Policy`. В `src/Composer/Policy/PolicyConfig.php` имя `minimum-release-age` явно перечислено среди **зарезервированных на будущее**:

```php
public const FUTURE_RESERVED_NAMES = [
    'package', 'packages',
    'license', 'licence', 'licenses', 'licences',
    'support', 'maintenance', 'security',
    'minimum-release-age',
];
```

То же самое продублировано в JSON-схеме `res/composer-schema.json` (внутри `config.policy`):

```json
"^(package|packages|license|licence|licenses|licences|support|maintenance|security|minimum-release-age)$": {
    "not": {},
    "description": "This name is reserved for future use and cannot be used as a custom dependency policy name."
}
```

Источник — первичный: файлы внутри `/home/feycot/.local/share/mise/installs/php/8.5.9/bin/composer` (Composer 2.10.2). Проверяется так:

```bash
cp "$(readlink -f "$(which composer)")" /tmp/composer.phar
php -r 'echo file_get_contents("phar:///tmp/composer.phar/src/Composer/Policy/PolicyConfig.php");' | grep -A10 FUTURE_RESERVED_NAMES
```

Практический смысл: `config.policy.minimum-release-age` **сейчас не работает и даже вызовет ошибку валидации** («reserved for future use»), но ключ уже застолблён — значит фича придёт именно под этим именем.

### 1.2. Что в 2.10 реально появилось вместо этого

Composer 2.10 принёс `config.policy` — единый объект вместо старого `config.audit` (тот помечен deprecated). Подключи: `advisories`, `malware`, `abandoned` + пользовательские списки. Дефолты по схеме:

| policy | `block` | `audit` |
| --- | --- | --- |
| `advisories` | `true` | `fail` |
| `malware` | `true` (+ `block-scope: all`) | `fail` |
| `abandoned` | `false` | `fail` |

- Первичный источник: [getcomposer.org/doc/06-config.md](https://getcomposer.org/doc/06-config.md) (раздел `policy`; `audit` там прямо помечен «Deprecated. Use `config.policy` instead»).
- Первичный источник: `res/composer-schema.json` внутри phar'а 2.10.2 (описания и `default` совпадают с доками).
- Анонс: [blog.packagist.com — Composer 2.10 Release](https://blog.packagist.com/composer-2-10-release/) — «native malware filtering», детект от Aikido.

Ни `policy`, ни `audit` **не содержат ничего про возраст версии**. Отдельно проверено: строк `cooldown` / `minimumReleaseAge` в phar'е нет, `minimum-release-age` встречается ровно в двух местах — оба выше, оба «зарезервировано».

### 1.3. Статус тикетов в composer/composer

Первичные источники — сам трекер:

| Issue | Дата | Статус | Комментарий |
| --- | --- | --- | --- |
| [#12552](https://github.com/composer/composer/issues/12552) «Add minimum release age for better protection against supply chain attacks» | 2025-09-24 | **Closed as not planned** | автор сам ушёл на Dependabot; там же всплыло `composer outdated --sort-by-age` |
| [#12633](https://github.com/composer/composer/issues/12633) «Implement dependency cooldowns in composer» | 2025-11-22 | **Open, milestone 2.11** | основной живой тикет |
| [#12847](https://github.com/composer/composer/issues/12847) «Feature request: minimum-release-age…» | 2026 | дубль | → #12633 |
| [#12877](https://github.com/composer/composer/issues/12877) «Add `minimumReleaseAge` config option…» | 2026-05-23 | **Closed as duplicate** of #12633 | ссылается на `npmMinimalAgeGate: 1d` по умолчанию в Yarn 4.15 |

Реализация лежит в двух несмёрженных PR — на 2026-08-25 не выбран даже подход:

| PR | Дата | Статус |
| --- | --- | --- |
| [#12692](https://github.com/composer/composer/pull/12692) «Add cooldown policy config to filter newly released packages», +2719 строк | 2025-12-27, обновлён 2026-07-20 | Open, milestone **2.11**, mergeable, ревью нет; автор предлагает разбить на части |
| [#13030](https://github.com/composer/composer/pull/13030) «Add minimum package age option», +356 строк | 2026-08-12 | Open, milestone не проставлен; автор признаёт пересечение с #12692 |

Дата релиза 2.11 не назначена: последний релиз — 2.10.2 (2026-07-01), RC на 2.11 нет, а в ветке `main` (`2.10.999-dev`) `minimum-release-age` по-прежнему только зарезервированное имя. Значит `composer self-update --snapshot` фичи тоже не даст.

### 1.4. Почему ещё не сделали — официальное объяснение

Пост Nils Adermann (naderman, автор Composer) и Igor Benko, [blog.packagist.com, 2026-05-27](https://blog.packagist.com/an-update-on-composer-packagist-supply-chain-security/):

> The first follow-up that we have already designed is a **minimum release age** policy (also known as cooldown period): refuse to install a version that was published less than N hours or days ago.

> The policy is currently blocked by prerequisite work on the Packagist.org side. We need release metadata to be reliably immutable before we can safely treat "publication time" as a security input.

Это первичный источник от команды Composer: фича **спроектирована**, заблокирована требованием иммутабельности метаданных на packagist.org, которая раскатывалась той же неделей.

**И она уже раскатана.** В `composer/packagist` (`src/Entity/Version.php`, ветка `main`) есть новый метод с говорящим docblock'ом:

```php
/**
 * Timestamp used as the release cooldown / minimum-release-age reference.
 *
 * Stable releases are immutable so their creation date is stable (a later source-URL
 * rewrite bumps updatedAt but not createdAt). Dev versions use the last-updated date so
 * every new commit restarts the cooldown.
 */
public function getPublishedAt(): \DateTimeImmutable
{
    return $this->isDevelopment() ? $this->updatedAt : $this->createdAt;
}
```

и он сериализуется в метаданные как `published-time`:

```php
$data['published-time'] = $this->getPublishedAt()->format('Y-m-d\TH:i:sP');
```

Источник первичный: <https://raw.githubusercontent.com/composer/packagist/main/src/Entity/Version.php>.

Поле **уже отдаётся в проде**, проверено запросом:

```bash
curl -s https://repo.packagist.org/p2/laravel/framework.json | head -c 400
# v13.26.1  time: 2026-08-18T20:31:28+00:00  published-time: 2026-08-18T20:32:11+00:00
```

**Итог:** предпосылка снята, ключ зарезервирован, тикет в milestone 2.11. Composer 2.10.2 про `published-time` ещё ничего не знает (строки нет ни в одном файле phar'а).

---

## 2. Что реально дают `composer audit` и `roave/security-advisories`

Коротко: **обе штуки работают по базе уже опубликованных advisory. Cooldown они не заменяют ни на сколько.**

### `composer audit`

- Сверяет установленные версии со списком security advisories (для packagist.org — [Packagist Security Advisories API](https://packagist.org/apidoc), плюс с 2.10 — флаги malware от Aikido), а также репортит abandoned-пакеты.
- Поведение настраивается через `config.policy.*.audit` = `ignore` / `report` / `fail`; `block` дополнительно выкидывает версии из пула резолвинга ещё до `update`. Источник: [06-config.md § policy](https://getcomposer.org/doc/06-config.md), схема в phar'е.
- Дефолты у нас **не заданы явно** — в `composer.json` нет ни `config.audit`, ни `config.policy`, работают дефолты Composer: `advisories.block=true`, `malware.block=true`, `abandoned.audit=fail`, `abandoned.block=false`. (Проверено: `grep -n audit composer.json` → пусто.) То есть утверждение «в composer.json уже есть `config.audit.abandoned=fail`» неверно — это просто дефолт Composer 2.10, а не наша настройка.

### `roave/security-advisories`

README (первичный источник, ветка `latest`: <https://github.com/Roave/SecurityAdvisories>):

> Simply add `"roave/security-advisories": "dev-latest"` to your `composer.json` `"require-dev"` section and you will not be able to harm yourself with software with known security vulnerabilities.

> The checks are only executed when adding a new dependency via `composer require` or when running `composer update`: deploying an application with a valid `composer.lock` and via `composer install` won't trigger any security versions checking.

Механика: пакет — это просто гигантский список `conflict` (в нашем локе — **1074 записи**), собираемый ежечасно. Composer физически не может разрешить конфликтующую версию.

Мелочь по нашему репозиторию: `composer.json` требует `roave/security-advisories: dev-master`, а README настаивает на `dev-latest` («This package can only be required in its `dev-latest` version»). `dev-master` на packagist ещё существует и у нас работает (1074 конфликта в локе), но это не то, что рекомендует апстрим. Не блокер, но стоит однажды переехать.

### Вывод по пункту 2

| | известная уязвимость | известная закладка (malware) | свежая закладка, о которой ещё никто не знает |
| --- | --- | --- | --- |
| `composer audit` / `policy.advisories` | ловит | — | **не ловит** |
| `policy.malware` (2.10) | — | ловит после детекта Aikido | **не ловит** |
| `roave/security-advisories` | ловит на `update`/`require` | — | **не ловит** |
| cooldown | — | — | ловит (окно детекта) |

Именно этот последний столбец и есть дыра. Формулировка из блога Packagist: «malicious versions are typically pulled or detected within hours of publication» — cooldown покупает ровно эти часы.

---

## 3. Private Packagist — умеет ли задерживать версии

**Нет, задержки публикации версий там нет.** По официальной документации/маркетинговым страницам packagist.com:

- [packagist.com/features/mirroring-composer-packages](https://packagist.com/features/mirroring-composer-packages): «By default packages are automatically mirrored and added to your Private Packagist repository the first time they are accessed through composer update». Настраиваемая mirroring policy позволяет требовать ручного добавления **нового пакета** администратором — но это про пакет целиком, а не про новые версии уже замирроренного пакета.
- Новые версии зеркалируемых с packagist.org пакетов появляются «within seconds»; для остальных источников — проверка минимум раз в 12 часов (это не политика безопасности, а частота опроса).
- Из security-фич названы: malware filtering, plugin allow-listing, verified downloads, [Security Monitoring](https://blog.packagist.com/security-monitoring/), [блокировка malware-загрузок](https://blog.packagist.com/blocking-malware-downloads-for-every-composer-version-in-private-packagist/) — cooldown в списке отсутствует.

Косвенный, но сильный признак: тот же самый пост команды Composer/Packagist ([2026-05-27](https://blog.packagist.com/an-update-on-composer-packagist-supply-chain-security/)) описывает minimum release age как **будущую** фичу, а не как то, что уже продаётся в Private Packagist.

*Помечаю честно:* отдельной страницы документации «minimum release age» на packagist.com я не нашёл; вывод «не поддерживается» строится на отсутствии упоминаний в docs/features + прямой формулировке блога о том, что фича ещё впереди.

---

## 4. Обходные пути средствами самого Composer

### 4.1. Constraint по дате в `composer.json` — невозможно

Схема Composer допускает только semver-constraint'ы. Поля `time` в `require` нет, версии по дате отбирать нечем. Источник: `res/composer-schema.json` (`definitions.package-versions`), [getcomposer.org/doc/articles/versions.md](https://getcomposer.org/doc/articles/versions.md). Аналога `npm install --before=<date>` в Composer не существует.

### 4.2. `--prefer-lowest` — не про то

Это опция `composer update` для CI-матрицы: ставит **минимально допустимые** версии, чтобы проверить, что нижняя граница constraint'ов честная. Никакого отношения к возрасту релиза. [03-cli.md § update](https://getcomposer.org/doc/03-cli.md#update-u).

### 4.3. `composer update --lock` — не про то

Только пересчитывает hash/метаданные лока без изменения версий. Полезно, но не защита.

### 4.4. Плагин Composer — **технически рабочий путь**

Composer 2.10.2, `src/Composer/Plugin/PluginEvents.php` (первичный источник, файл из phar'а):

```php
const INIT = 'init';
const COMMAND = 'command';
const PRE_FILE_DOWNLOAD = 'pre-file-download';
const POST_FILE_DOWNLOAD = 'post-file-download';
const PRE_COMMAND_RUN = 'pre-command-run';
const PRE_POOL_CREATE = 'pre-pool-create';
```

Нужный хук — `PRE_POOL_CREATE`. `src/Composer/Plugin/PrePoolCreateEvent.php` даёт ровно то, что требуется:

```php
public function getPackages(): array          // все кандидаты до резолвинга
public function setPackages(array $packages): void   // отфильтрованный список
public function getUnacceptableFixedPackages(): array
public function setUnacceptableFixedPackages(array $packages): void
```

А у каждого пакета есть `PackageInterface::getReleaseDate()`, заполняемый из поля `time` метаданных (`src/Composer/Package/Loader/ArrayLoader.php`, строки ~243-247: `$config['time']` → `new \DateTime(...)`).

Значит плагин на ~80-120 строк реален: подписаться на `pre-pool-create`, выкинуть из `getPackages()` всё, у чего `getReleaseDate()` свежее N дней, вернуть через `setPackages()`. Plugin API 2.9.0, потребуется прописать себя в `config.allow-plugins` ([articles/plugins.md](https://getcomposer.org/doc/articles/plugins.md)).

Оговорки, из-за которых **писать его не стоит**:
- фильтрация по `time` — это дата тега в VCS, а не дата публикации на Packagist (см. § 6), т.е. злоумышленник может её подделать. Именно поэтому апстрим и ждал `published-time`;
- слишком агрессивный фильтр пула ломает резолвинг с невнятными сообщениями «package not found»;
- 2.11 принесёт то же самое официально и с правильным полем времени.

*Помечаю:* `PRE_POOL_CREATE` не описан в `articles/plugins.md` (там разобран только `PRE_FILE_DOWNLOAD`) — сигнатуры взяты напрямую из исходников в phar'е, это первичный источник, но не документация.

### 4.5. Свой repository-прокси

Технически: поднять [composer/satis](https://github.com/composer/satis) (репозиторий под организацией composer) или Private Packagist self-hosted, отдавать метаданные с вырезанными свежими версиями. Работает, но это отдельный сервис, деплой и обслуживание ради 14 дней задержки. Для проекта такого размера — заведомый оверкилл.

---

## 5. Готовые сторонние плагины

Полный список того, что вообще есть на packagist по теме (запрос к официальному search API):

```bash
curl -s 'https://packagist.org/search.json?q=cooldown&type=composer-plugin'
# {"results":[{"name":"cooldown/plugin",...,"downloads":0,"favers":0},
#             {"name":"cooldownio/plugin",...,"downloads":0,"favers":0}],"total":2}
```

Оба — один и тот же зачаток:

- [github.com/cooldownio/plugin](https://github.com/cooldownio/plugin) — 1 коммит, 0 звёзд, 0 форков, стабильных релизов нет. Из README: «Early proof-of-concept — not ready for use», «installs cleanly but does nothing yet: the plugin class is an empty stub», «Don't depend on it in real projects».
- `cooldown/plugin` (репозиторий `black-bits/cooldown-plugin`) — 0 загрузок, зеркало того же.

**Вывод: готовых плагинов нет.** Ноль загрузок и пустой класс плагина — это не «малоизвестное решение», это заглушка под будущее имя.

---

## 6. Самый дешёвый практичный вариант: даты уже под рукой

### 6.1. `composer.lock` содержит `time` — проверено фактически

```bash
$ grep -c '"time"' composer.lock
203
$ grep -m3 '"time"' composer.lock
            "time": "2026-06-14T18:21:03+00:00"
            "time": "2024-02-09T16:56:22+00:00"
            "time": "2023-12-20T15:40:13+00:00"
```

Что это за поле, по первичным источникам:

- JSON-схема Composer (`res/composer-schema.json`): `"time": { "type": "string", "description": "Package release date, in 'YYYY-MM-DD', 'YYYY-MM-DD HH:MM:SS' or 'YYYY-MM-DDTHH:MM:SSZ' format." }`
- Packagist кладёт туда `Version::getReleasedAt()`, а это **дата тега/коммита в VCS**, не дата публикации на packagist.org. Отдельно и позже добавлено поле `published-time` = `Version::getPublishedAt()` — вот оно и есть дата публикации (`composer/packagist`, `src/Entity/Version.php`, строки ~303-306 и ~639-654).
- Разница на практике мала: `laravel/framework v13.26.1` → `time: 2026-08-18T20:31:28Z`, `published-time: 2026-08-18T20:32:11Z` — 43 секунды (вебхук отрабатывает мгновенно).

**Важное ограничение:** `published-time` в `composer.lock` **нет** (`grep -c published-time composer.lock` → `0`), и Composer 2.10.2 про это поле вообще не знает — строка не встречается ни в одном файле phar'а. Значит офлайн доступна только `time`, т.е. дата VCS-тега. Для защиты от случайного «взял свежак» этого достаточно; для защиты от целенаправленного бэкдейта тега — нет. Это ровно та причина, по которой апстрим и не выпускал фичу раньше.

### 6.2. Ещё лучше: даты отдаёт сам Composer, без похода в API

`composer show` умеет отдавать даты релизов машиночитаемо. Из `src/Composer/Command/ShowCommand.php` (строки ~493, ~543-565):

```php
$writeReleaseDate = $writeLatest && ($input->getOption('sort-by-age') || $format === 'json');
...
$packageViewData['release-date']        = $package->getReleaseDate()->format(DateTimeInterface::ATOM);
$packageViewData['latest-release-date'] = $latestPackage->getReleaseDate()->format(DateTimeInterface::ATOM);
```

То есть при `--latest --format=json` даты попадают в вывод автоматически. Проверено на нашем проекте:

```json
{
    "name": "barryvdh/laravel-debugbar",
    "direct-dependency": true,
    "version": "v4.4.1",
    "release-age": "3 weeks old",
    "release-date": "2026-08-03T06:23:16+00:00",
    "latest": "v4.4.2",
    "latest-status": "semver-safe-update",
    "latest-release-date": "2026-08-20T12:02:04+00:00",
    "abandoned": false
}
```

197 записей, включая транзитивные (`direct-dependency: false`). Есть и `--sort-by-age` / `-A` для человекочитаемого вывода (та самая опция, которую упомянули в #12552).

Это лучше ручного разбора лока: даёт дату **той версии, на которую апдейт бы поехал** (`latest-release-date`), а не только установленной.

### 6.3. Packagist API — если всё-таки понадобится

Первичный источник: <https://packagist.org/apidoc>.

- Рекомендованный для тулинга эндпоинт: `GET https://repo.packagist.org/p2/{vendor}/{package}.json` — «the preferred way to access the data as it is always up to date, and dumped to static files so it is very efficient on our end».
- Формат: `{"packages": {"vendor/name": [ {...версии...} ]}, "minified": "composer/2.0"}`. Ответ minified — распаковывается `MetadataMinifier::expand()` из `composer/metadata-minifier`. В каждой версии есть `time` и (новое) `published-time`.
- Числовых rate limit'ов в apidoc не опубликовано. Рекомендации: не более 10 одновременных запросов (20 для статики), не запускать по расписанию ровно в XX:00 и в полночь, слать `User-Agent` с контактом, использовать HTTP/2 и `If-Modified-Since`.

Но при подходе из 6.2 ходить туда руками не нужно вовсе — Composer сам сделает эти запросы со своим кэшем.

---

## Рекомендация

**Ленивый вариант, который работает: один `make`-таргет поверх `composer show --latest --format=json`.**

Что он делает: печатает пакеты, у которых доступная свежая версия младше 14 дней, и возвращает ненулевой код. Запускается руками перед `composer update` или после него в CI. Ноль новых зависимостей, ноль запросов к API руками, ноль плагинов.

Рецепт обязан быть **одной физической строкой**: внутри `php -r '...'` одинарные кавычки не дают шеллу склеить переносы через `\`, и обратный слэш утечёт в PHP-код («syntax error, unexpected token "\"»). Все `$` в make-рецепте удваиваются.

```make
COOLDOWN_DAYS ?= 14

composer-cooldown:
	@composer show --latest --format=json --no-interaction | COOLDOWN_DAYS=$(COOLDOWN_DAYS) php -r '$$d = json_decode(stream_get_contents(STDIN), true)["installed"] ?? []; $$days = (int) getenv("COOLDOWN_DAYS"); $$cut = new DateTimeImmutable("-$$days days"); $$bad = 0; foreach ($$d as $$p) { if (!isset($$p["latest-release-date"]) || ($$p["latest-status"] ?? "") === "up-to-date") { continue; } if (new DateTimeImmutable($$p["latest-release-date"]) > $$cut) { printf("%-45s %s -> %s (%s)\n", $$p["name"], $$p["version"], $$p["latest"], $$p["latest-release-date"]); $$bad += 1; } } echo $$bad === 0 ? "cooldown ok\n" : "$$bad package(s) younger than $$days days\n"; exit($$bad === 0 ? 0 : 1);'
```

Проверено фактически на этом репозитории (`make -C /home/feycot/projects/hexlet-sicp -f <файл> composer-cooldown`): 47 пакетов из 197 моложе 14 дней, exit code 1. Пример вывода:

```
guzzlehttp/guzzle                             7.15.3 -> 8.1.0 (2026-08-24T11:07:02+00:00)
laravel/framework                             v13.25.0 -> v13.26.1 (2026-08-18T20:31:28+00:00)
symfony/http-kernel                           v8.1.4 -> v8.1.5 (2026-08-22T13:45:00+00:00)
...
47 package(s) younger than 14 days
```

Объём работ: **4 строки в Makefile, полчаса вместе с прогоном.** Требует сети (Composer сходит за метаданными латестов) и `vendor/` на месте.

Оговорки, которые я бы принял осознанно:

- Это **предупреждение, а не блокировка**. Заблокировать резолвинг без плагина нельзя. Для ручного `composer update` предупреждения достаточно — решение всё равно принимает человек.
- Проверка идёт по `time` (дата VCS-тега), а не по `published-time` (дата публикации). От случайного свежака защищает, от подделанной даты тега — нет.
- Пакеты на `dev-master` (`roave/security-advisories`, `slevomat/coding-standard`) в список попадают всегда — у dev-версий «релиз» это последний коммит. Оба уже в `ignoreDeps` Renovate, при желании их так же легко отфильтровать по имени.
- Если хочется вообще без сети и без `vendor/` — вторая, ещё более тупая версия: пройтись по `time` в `composer.lock` до и после `composer update` и сравнить с порогом. Хуже тем, что видно только уже вкатившееся, а не то, что вкатится.

Чего делать **не надо**:

- **Не писать свой плагин.** `PRE_POOL_CREATE` + `setPackages()` это позволяют, но 2.11 привезёт `config.policy.minimum-release-age` официально и с правильным полем времени. Имя ключа уже зарезервировано в 2.10.2, предпосылка (`published-time`) уже в проде на packagist.org.
- **Не ставить `cooldownio/plugin` / `cooldown/plugin`** — пустые заглушки, 0 загрузок, автор сам пишет «not ready for use».
- **Не поднимать Satis/прокси** ради 14 дней задержки.

**Что стоит сделать заодно, раз уж полезли:**

- Явно зафиксировать политику в `composer.json` вместо опоры на дефолты, например `config.policy.abandoned.block = true` — сейчас `block` по умолчанию `false`, и abandoned-пакет спокойно установится, `audit` про него только пожалуется постфактум.
- Когда выйдет Composer 2.11 — снести таргет и заменить на `config.policy.minimum-release-age` (следить за [composer/composer#12633](https://github.com/composer/composer/issues/12633)).
- Переехать с `roave/security-advisories: dev-master` на `dev-latest`, как требует README апстрима.
