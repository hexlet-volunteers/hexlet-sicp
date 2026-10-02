<p align="center">
  <img src="https://playwright.dev/img/playwright-logo.svg" alt="Playwright Logo" width="220"/>
</p>

<h1 align="center">Playwright Test Suite</h1>

Коллекция автоматизированных e2e на базе **Playwright**.  
Репозиторий предназначен для запуска тестов в разных режимах (headless / headed),
генерации отчетов, снятия скриншотов и отладки падений.
Коллекция тестов будет обновляться и дополняться по мере развития проекта и необходимости.

---

## 📁 Структура проекта

```text
.
├── tests/                  # Коллекция тестов
│   ├── screenshots/        # скриншоты успешных тестов
│   ├── auth/               # Тесты паролей
│   ├── book/               # Тесты основных функций книги
│   └── profile/            # Тесты профиля
│
├── playwright.report/      # артефакты HTML-отчёта Playwright
├── test-results/           # Репорты о пройденных тестах
│
├── playwright.config.js    # Конфигурация Playwright
├── package.json
└── package-lock.json
```
## Требования

* Node.js ≥ 18
* npm ≥ 9
* Linux / macOS / Windows

#### Проверить версии:
```
node -v
npm -v
```

## Установка

#### Установить зависимости:
```
npm install
```

#### Установить браузеры Playwright:
```
npx playwright install
```

(один раз или при обновлении версии Playwright)

## ▶️ Запуск тестов
#### Запуск всех тестов (headless, по умолчанию)
```
npx playwright test
```
#### Запуск в headed-режиме (с UI браузера)
```
npx playwright test --headed
```
#### Запуск одного файла тестов
```
npx playwright test tests/auth/login.spec.js
```
#### Запуск тестов по папке
```
npx playwright test tests/smoke
```
#### Запуск в режиме UI (Test Runner)
```
npx playwright test --ui
```
*Удобно для локальной отладки и просмотра шагов.*

#### Запуск в режиме отладки
```
npx playwright test --debug
```
* браузер открывается пошагово
* можно использовать ```page.pause()```

## 📷 Скриншоты

В проекте включены screenshots с результатами тестов. После успешного завершения тестов они сохраняются в директории *tests/screenshots*

### 📊 HTML-отчет

После прогона:
```
npx playwright show-report
```

#### Отчет содержит:
* статус тестов
* шаги
* скриншоты

### 🚀 Полезные команды
```
# Проверить версии
npx playwright --version

# Сгенерировать тест кодогеном
npx playwright codegen https://example.com

# Очистить кеш и артефакты
rm -rf playwright-report test-results
```
## 📌 Дополнительно

Документация: https://playwright.dev

API Reference: https://playwright.dev/docs/api/class-playwright

## Прогон против стенда

По умолчанию спеки ходят в продакшен `https://sicp.hexlet.io`. Адрес задаёт `PLAYWRIGHT_BASE_URL`, учётку для входа — `PLAYWRIGHT_USER_EMAIL` и `PLAYWRIGHT_USER_PASSWORD` (по умолчанию — тестовый пользователь прода). Пути в спеках относительные, русская локаль выбирается через `locale: 'ru-RU'` в конфиге. Спеки, которые заводят и удаляют аккаунты, на проде пропускаются.

Против локального приложения с сидами:

```bash
php artisan serve --port=8000
cd tests/playwright/sicp_playwright && npm ci && npx playwright install chromium
PLAYWRIGHT_BASE_URL=http://127.0.0.1:8000 PLAYWRIGHT_USER_EMAIL=admin@test.com PLAYWRIGHT_USER_PASSWORD=password \
  npm test -- --reporter=list
```

`--reporter=list` обязателен в терминале: html-репортёр при падении поднимает сервер отчёта и подвешивает команду. Спеки регистрации оставляют пользователей в базе — повторный прогон упрётся в «email занят», пока их не удалить.

## 👤 Контакты / Авторы тестов

Команда: QA Hexlet SICP

Ответственный: [Миша](https://github.com/Michael57e)