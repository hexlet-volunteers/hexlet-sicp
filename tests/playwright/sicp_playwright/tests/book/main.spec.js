import { test, expect } from '@playwright/test';
import { user } from '../stand';

test('dashboard visibility on main', async ({ page }) => {
  await page.goto('/');

  // Login
  await page.getByRole('link', { name: 'Вход' }).click();
  await page.getByRole('textbox', { name: 'Электронная почта' }).click();
  await page.getByRole('textbox', { name: 'Электронная почта' }).fill(user.email);
  await page.getByRole('textbox', { name: 'Пароль' }).click();
  await page.getByRole('textbox', { name: 'Пароль' }).fill(user.password);
  await page.getByRole('button', { name: 'Отправить' }).click();

  // Найти дашборд
  const dashboardGraph = page.locator('div.graph');
  await expect(dashboardGraph).toBeVisible({ timeout: 10000 });

  // Cделать скрин результата
  await page.screenshot({ path: './screenshots/dashboard_is_visible.png', fullPage: true });
});


test('from main to log', async ({ page }) => {
  // Main
  await page.goto('/');

  // Login
  await page.getByRole('link', { name: 'Вход' }).click();
  await page.getByRole('textbox', { name: 'Электронная почта' }).fill(user.email);
  await page.getByRole('textbox', { name: 'Пароль' }).fill(user.password);
  await page.getByRole('button', { name: 'Отправить' }).click();

  //
  await page.getByRole('link', { name: 'История активности' }).click();

  // Проверяем, что перешли на верную страницу
  await expect(page).toHaveURL(/\/log$/);

  // Делаем скриншот конечной страницы
  await page.screenshot({ path: './screenshots/from_main_to_log.png', fullPage: true });

});


test('from main to comments', async ({ page }) => {
  // Main
  await page.goto('/');

  // Login
  await page.getByRole('link', { name: 'Вход' }).click();
  await page.getByRole('textbox', { name: 'Электронная почта' }).fill(user.email);
  await page.getByRole('textbox', { name: 'Пароль' }).fill(user.password);
  await page.getByRole('button', { name: 'Отправить' }).click();

  await page.getByRole('link', { name: 'Последние комментарии' }).click();

  // Проверяем, что перешли на верную страницу
  await expect(page).toHaveURL(/\/comments$/);

  // Делаем скриншот конечной страницы
  await page.screenshot({ path: './screenshots/from_main_to_comments.png', fullPage: true });
});