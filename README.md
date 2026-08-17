# Hexlet SICP

[![github action status](https://github.com/Hexlet/hexlet-sicp/actions/workflows/ci.yml/badge.svg)](https://github.com/Hexlet/hexlet-sicp/actions)

[![codecov](https://codecov.io/gh/Hexlet/hexlet-sicp/branch/master/graph/badge.svg)](https://codecov.io/gh/Hexlet/hexlet-sicp)

[![Deploy](https://www.herokucdn.com/deploy/button.svg)](https://heroku.com/deploy)

Read this in another language: [Русский](README.ru.md)

Hexlet SICP is a service for those studying the book Structure and Interpretation of Computer Programs. Track your progress and match yourself against others on the global leaderboard.

[How to study _Structure and Interpretation of Computer Programs_ (SICP)](https://guides.hexlet.io/how-to-learn-sicp/)

## For contributors

* Discuss the project on Telegram: https://t.me/hexletcommunity/12

### Recorded meetings

* [Recorded meetings playlist](https://www.youtube.com/playlist?list=PL37_xn2SVZdCJ-xgB-phFaWrp25Kc3cLk)

## FAQ

Q: I get this error `Illuminate\Session\TokenMismatchException: CSRF token mismatch.`
A: Reset your config cache `php artisan config:clear`

## Requirements

Run `composer check-platform-reqs` to check PHP deps:

* PHP ^8.3
* Composer
* Node.js (v16+) & NPM (6+)
* PostgreSQL (locally you can run it from `docker compose`, see below)
* [heroku cli](https://devcenter.heroku.com/articles/heroku-cli#download-and-install); [How to deploy Laravel on Heroku](https://ru.hexlet.io/blog/posts/kak-razvernut-prilozhenie-laravel-na-heroku) (in Russian)

[What is a Version Manager?](https://guides.hexlet.io/version-managers/)

## Setup

### Local setup

The app runs on PostgreSQL everywhere: locally, in tests, on stage and in production.
The simplest way to get a database is the container from `docker compose` — it listens
on `127.0.0.1:54320` and creates both databases, the application one and the test one.

```sh
make compose-start-database # start PostgreSQL in a container
make setup                  # set up the project
make start                  # start server at http://127.0.0.1:8000/
make test                   # run tests
```

Connection settings are taken from `.env`, which `make setup` copies from `.env.example`.
Their defaults match the container above, so nothing has to be edited.

To use your own PostgreSQL instead of the container, change `DB_*` and `TEST_DB_*` in `.env`
(the default port is 5432) and create both databases by hand:

```sh
createdb hexlet_sicp
createdb hexlet_sicp_test
```

Tests recreate the schema on every run, so they use a separate database — never point
`TEST_DB_DATABASE` at the one you develop against.

### Setup in Docker

```sh
make compose-setup # build project
make compose-start # start server at http://127.0.0.1:8000/
make compose-bash  # start bash session inside docker container
make test          # run tests inside docker container
```

Inside the containers the database host is set by `docker-compose.yml`, so the `DB_HOST`
and `DB_PORT` values from `.env` are not used there.

### Stage deployment

Stage environment uses Docker Compose to deploy a production-like setup on a single server.

**Architecture:**
- `app` - Laravel application container (serves HTTP on port 80)
- `queue` - background queue worker
- `db` - PostgreSQL database

**Initial setup** (first time only):

1. Prepare the `.env` file on the server

    ```sh
    cp .env.example .env
    nano .env
    ```

2. Configure environment variables (see `docker-compose.stage.yml`)

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

3. Run initial setup

    ```sh
    make stage-setup
    ```

    This will:
    - Start all containers
    - Generate application key
    - Run migrations with seeders
    - Create storage symlink
    - Build config/route/view caches

**Deploying updates:**

```sh
make stage-deploy
```

This will:
- Pull latest code from git (main branch)
- Rebuild containers
- Run new migrations
- Clear and rebuild caches

**Deploying a specific branch:**

```sh
make stage-deploy-branch BRANCH=feature/my-feature
```

This will:
- Fetch and checkout the specified branch
- Pull latest code from that branch
- Rebuild containers
- Run new migrations
- Clear and rebuild caches

**Other commands:**

```sh
make stage-start    # Start containers
make stage-stop     # Stop containers
make stage-restart  # Restart containers
make stage-logs     # View logs
make stage-bash     # Open bash in app container
make stage-down     # Stop and remove containers
make stage-reset    # Complete rebuild: stop, remove volumes, rebuild, and reinitialize
```

**HTTPS setup:**

The stage app listens on HTTP (port 80) only. For HTTPS, use an external reverse proxy (Caddy, Traefik, or host-level nginx) for TLS termination.

## Coding stardards and other rules

* Pull requests should be as small as reasonably possible
* All code must comply with the PSR12 and Laravel standards (we also use some custom rules for added challenge)
* Every pull request must pass all tests
* All controller actions must have test coverage ([Start writing (appropriate) tests](https://ru.hexlet.io/blog/posts/how-to-test-code) (in Russian))
* The forms are made using [laraeast/laravel-bootstrap-forms](https://github.com/laraeast/laravel-bootstrap-forms)
* RoR's resource routing convention is used for the most part. In a very rare case when a route doesn't seem to fit the convention, it should be discussed first
* All strings must be stored in locale files
* To enable Rollbar logging, set the variable `LOG_CHANNEL=rollbar` and `ROLLBAR_TOKEN=` ([docs](https://docs.rollbar.com/docs/laravel))
* To add an exercise, put its contents (a listing or pic) at `resources/views/exercise/listing/#_#.blade.php` and its text description, at `resources/lang/{locale}/sicp.php` under the key `exercises.#.#` (mind the locale).
* To generate helper files (for autocompletion), use `make ide-helper`
* Run `php artisan` and check out all available commands!

## GitHub Auth Setup Guide

Integrate the app with your GitHub account (read more at https://developer.github.com/apps/about-apps/). To integrate the app:

* Enter your GitHub account and go to _Settings_
* On the right, choose _GitHub Apps_, then push _New GitHub App_
* A form will pop up. In the _GitHub App name_ field, type the app name (for example, _Hexlet-SICP_)
* In _Homepage URL_, put the web address hosting your deploy (for example, _https://hexlet-sicp.herokuapp.com_)
* In _User authorization callback URL_, put the URL to redirect to, once a user authorizes via GitHub. (for example, _https://hexlet-sicp.herokuapp.com/oauth/github/callback_)
* In _Webhook URL_, put the URL to dispatch events to (for example, _https://hexlet-sicp.herokuapp.com/oauth/github/callback_)
* Set the permission to access email (_User permissions->Email addresses->Read only_)
* Save the app settings (push _Create GitHub App_)
* The app page will open. Copy the Client ID and Client secret
* Generate a private key (push _Generate a private key_)

If deployed on Heroku, set the environment variables for your deploy. To set environment variables:

* Open _Settings_
* In the _Config Vars_ setting, add the following variables: GITHUB_CLIENT_ID, GITHUB_CLIENT_SECRET and GITHUB_URL_REDIRECT and set them to the respective Client ID, Client secret and User authorization callback URL
* Then, reset the configuration cache: ```heroku run php artisan config:cache```

### Setting up a testing database

Tests always run on the `pgsql_test` connection — `phpunit.xml` selects it, no extra flags needed.
The container from `docker compose` already has the `hexlet_sicp_test` database, so `make test`
works right after `make setup`.

For a locally installed PostgreSQL, create the database once and point `TEST_DB_*` in `.env` at it:

```shell
sudo apt install postgresql
sudo -u postgres createuser --createdb $(whoami)
createdb hexlet_sicp_test
```

### Adding a pre-commit hook

```shell
git config core.hooksPath .githooks
```

##

[![Hexlet Ltd. logo](https://raw.githubusercontent.com/Hexlet/assets/master/images/hexlet_logo128.png)](https://hexlet.io/?utm_source=github&utm_medium=link&utm_campaign=exercises-sicp)

This repository is created and maintained by the team and the community of Hexlet, an educational project. [Read more about Hexlet](https://hexlet.io/?utm_source=github&utm_medium=link&utm_campaign=exercises-sicp).
...
