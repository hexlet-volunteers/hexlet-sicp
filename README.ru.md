# Hexlet SICP

[![github action status](https://github.com/Hexlet/hexlet-sicp/actions/workflows/master.yml/badge.svg)](https://github.com/Hexlet/hexlet-sicp/actions)

[![codecov](https://codecov.io/gh/Hexlet/hexlet-sicp/branch/master/graph/badge.svg)](https://codecov.io/gh/Hexlet/hexlet-sicp)

[![Deploy](https://www.herokucdn.com/deploy/button.svg)](https://heroku.com/deploy)

Читать на другом языке: [English](README.md)

Hexlet SICP &mdash; это сервис для тех, кто изучает книгу "Структура и интерпретация компьютерных программ". Отслеживайте свой прогресс и сравнивайте себя с другими в глобальной таблице лидеров.

Предварительно рекомендуем прочесть статью [Как изучать Структуру и Интерпретацию Компьютерных Программ (SICP)](https://guides.hexlet.io/how-to-learn-sicp/)

## Участие в разработке

* Обсуждение идёт в [Telegram Hexlet](https://t.me/hexletcommunity/12) в канале Волонтеры
* [Плейлист записей созвонов участников](https://www.youtube.com/playlist?list=PL37_xn2SVZdCJ-xgB-phFaWrp25Kc3cLk)

## Известные ошибки

Q: Ошибка `Illuminate\Session\TokenMismatchException: CSRF token mismatch.`

A: Сбросить кеш конфига `php artisan config:clear`

## Установка

### Предварительные требования

* PHP ^8.3
* Composer
* Node.js (v16+) & NPM (6+)
* PostgreSQL (локально можно поднять из `docker compose`, см. ниже)
* Heroku cli ([_Как развернуть приложение Laravel на Heroku_](https://ru.hexlet.io/blog/posts/kak-razvernut-prilozhenie-laravel-na-heroku))

Проверить зависимости PHP можно командой `composer check-platform-reqs`

Если нет каких-то зависимостей, то их можно установить командой (Ubuntu) `sudo apt install php-EXTENSION`

### Локальная установка

Приложение везде работает на PostgreSQL: локально, в тестах, на stage и в проде.
Проще всего поднять базу контейнером из `docker compose` — он слушает `127.0.0.1:54320`
и заводит сразу две базы: рабочую и тестовую.

```sh
make compose-start-database # запуск PostgreSQL в контейнере
make setup                  # первоначальная установка
make start                  # запуск сервера http://127.0.0.1:8000/
make test                   # запуск тестов
```

Параметры подключения берутся из `.env`, который `make setup` копирует из `.env.example`.
Значения по умолчанию совпадают с контейнером выше, поэтому править ничего не нужно.

Чтобы работать со своим PostgreSQL, поменяйте в `.env` переменные `DB_*` и `TEST_DB_*`
(порт по умолчанию — 5432) и создайте обе базы вручную:

```sh
createdb hexlet_sicp
createdb hexlet_sicp_test
```

Тесты пересоздают схему на каждом прогоне, поэтому база для них отдельная — не указывайте
в `TEST_DB_DATABASE` ту, в которой ведёте разработку.

### Установка в Docker

```sh
make compose-setup # собрать проект
make compose-start # запустить сервер http://127.0.0.1:8000/
make compose-bash  # запустить сессию bash в docker-контейнере
make test          # запустить тесты в docker-контейнере
```

Внутри контейнеров адрес базы задаёт `docker-compose.yml`, поэтому значения `DB_HOST`
и `DB_PORT` из `.env` там не используются.

### Развертывание stage окружения

Stage окружение использует Docker Compose для развертывания production-like конфигурации на одном сервере.

**Архитектура:**
- `app` - контейнер с Laravel приложением (обслуживает HTTP на порту 80)
- `queue` - воркер для обработки фоновых задач
- `db` - база данных PostgreSQL

**Первоначальная установка** (только один раз):

1. Подготовить файл `.env` на сервере

    ```sh
    cp .env.example .env
    nano .env
    ```

2. Настроить переменные окружения (см. `docker-compose.stage.yml`)

    ```dotenv
    APP_ENV=production
    APP_DEBUG=false
    APP_URL=https://your-domain.com
    PRODUCTION_URL=https://your-domain.com

    DB_CONNECTION=pgsql
    DB_HOST=db
    DB_PORT=5432
    DB_DATABASE=hexlet_sicp
    DB_USERNAME=sicp_user
    DB_PASSWORD=your_secure_password

    QUEUE_CONNECTION=database
    SESSION_DRIVER=cookie
    ```

3. Запустить первоначальную установку

    ```sh
    make stage-setup
    ```

    Эта команда:
    - Запустит все контейнеры
    - Сгенерирует ключ приложения
    - Применит миграции с сидами
    - Создаст символическую ссылку для storage
    - Построит кэши конфигурации/роутов/views

**Деплой обновлений:**

```sh
make stage-deploy
```

Эта команда:
- Подтянет последний код из git (ветка main)
- Пересоберет контейнеры
- Применит новые миграции
- Очистит и пересоздаст кэши

**Деплой конкретной ветки:**

```sh
make stage-deploy-branch BRANCH=feature/my-feature
```

Эта команда:
- Получит и переключится на указанную ветку
- Подтянет последний код из этой ветки
- Пересоберет контейнеры
- Применит новые миграции
- Очистит и пересоздаст кэши

**Другие команды:**

```sh
make stage-start    # Запустить контейнеры
make stage-stop     # Остановить контейнеры
make stage-restart  # Перезапустить контейнеры
make stage-logs     # Просмотр логов
make stage-bash     # Открыть bash в контейнере app
make stage-down     # Остановить и удалить контейнеры
make stage-reset    # Полная пересборка: остановка, удаление volumes, пересборка и инициализация
```

**Настройка HTTPS:**

Stage приложение слушает только HTTP (порт 80). Для HTTPS используйте внешний reverse proxy (Caddy, Traefik или nginx на хосте) для TLS терминации.

## Стандарты кодирования и прочие правила

* Пулреквесты должны быть настолько маленькими, насколько это возможно с точки зрения здравого смысла
* Весь код должен соответствовать стандартам кодирования PSR12 и Laravel (мы так же используем некоторые собственные правила, чтобы усложнить жизнь разработчика)
* Пулреквест должен проходить все проверки CI
* Все экшены контроллеров должны быть покрыты тестами ([_Начинаем писать тесты (правильно)_](https://ru.hexlet.io/blog/posts/how-to-test-code))
* Формы делаются с помощью [laraeast/laravel-bootstrap-forms](https://github.com/laraeast/laravel-bootstrap-forms)
* В подавляющем большинстве используется ресурсный роутинг. Что под него не подходит, сначала обсуждается (такое бывает крайне редко)
* Тексты только через локали
* Чтобы включить логирование Rollbar, необходимо установить переменную `LOG_CHANNEL=rollbar` и `ROLLBAR_TOKEN=` ([_документация_](https://docs.rollbar.com/docs/laravel))
* Чтобы добавить упражнение, необходимо добавить его содержимое (код или картинка) по пути `resources/views/exercise/listing/#_#.blade.php`, а текстовое описание в `resources/lang/{locale}/sicp.php` под ключем `exercises.#.#` на соответствующем языке.
* Для генерации хелперов (для автодополнения) используйте `make ide-helper`
* Изучите список доступных команд `php artisan`!

## Руководство по настройке авторизации через GitHub

Зарегистрируйте приложение на GitHub (подробнее <https://developer.github.com/apps/about-apps/>).

Для этого:

* В меню учетной записи GitHub выберите пункт “Settings”
* В открывшемся окне в панели навигации, справа, выберите “GitHub Apps”, затем нажмите кнопку “New GitHub App”
* В открывшейся форме в поле "GitHub App name" введите название приложения (например, Hexlet-SICP)
* В поле Homepage URL &mdash; адрес ресурса (например, <https://hexlet-sicp.herokuapp.com>)
* В поле "User authorization callback URL" введите полный URL-адрес для перенаправления после того, как пользователь авторизует приложение на GitHub. (например, <https://hexlet-sicp.herokuapp.com/oauth/github/callback>)
* В поле "Webhook URL" укажите URL-адрес, по которому будут отправляться события (например, <https://hexlet-sicp.herokuapp.com/oauth/github/callback>)
* Откройте права на получение информации о e-mail пользователя (User permissions->Email addresses->Read only)
* Сохраните данные регистрации приложения (кнопка "Create GitHub App")
* На открывшейся странице приложения скопируйте Client ID и Client secret
* Cгенерируйте закрытый ключ (кнопка Generate a private key)

В случае деплоя на Heroku, задайте переменные окружения для развернутого приложения.

Для этого:

* Перейдите на вкладку "Settings"
* В настройке "Config Vars" добавьте переменные `GITHUB_CLIENT_ID`, `GITHUB_CLIENT_SECRET` и `GITHUB_URL_REDIRECT` указав для них соответвенно значения Client ID, Client secret и User authorization callback URL
* После чего выпольните сброс кеша конфигурации: `heroku run php artisan config:cache`

### База данных для тестов

Тесты всегда идут на соединении `pgsql_test` — его выбирает `phpunit.xml`, дополнительных
флагов не нужно. В контейнере из `docker compose` база `hexlet_sicp_test` уже создана,
поэтому `make test` работает сразу после `make setup`.

Для локально установленного PostgreSQL базу нужно завести один раз и прописать её
в переменных `TEST_DB_*` файла `.env`:

```shell
sudo apt install postgresql
sudo -u postgres createuser --createdb $(whoami)
createdb hexlet_sicp_test
```

### Добавить пре-комит хук

Для хуков требуется локальный NodeJS

```shell
make setup-git-hooks
```

[![Hexlet Ltd. logo](https://raw.githubusercontent.com/Hexlet/assets/master/images/hexlet_logo128.png)](https://hexlet.io/?utm_source=github&utm_medium=link&utm_campaign=exercises-sicp)

This repository is created and maintained by the team and the community of Hexlet, an educational project. [Read more about Hexlet](https://hexlet.io/?utm_source=github&utm_medium=link&utm_campaign=exercises-sicp)

See most active contributors on [hexlet-friends](https://friends.hexlet.io/).
