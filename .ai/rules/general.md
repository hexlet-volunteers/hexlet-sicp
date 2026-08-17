---
paths:
  - .mcp.json
---

# General

## claude mcp list не проверяет авторизацию HTTP-серверов
Для `allure-testops` (HTTP MCP, токен подставляется из `${ALLURE_TESTOPS_TOKEN}`) `claude mcp list` печатает `✔ Connected` даже когда переменная не задана и заголовок уходит нерасширённым. Статус в списке — это не сигнал об успешной аутентификации.

Проверять подключение только вызовом инструмента, например `testops_get_project` (в дочерней сессии: `claude -p --permission-mode default ... --allowedTools "mcp__allure-testops__testops_get_project"`). `claude mcp get` тоже бесполезен: он показывает конфиг до подстановки.
