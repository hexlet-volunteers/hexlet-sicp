import { test, expect } from '@playwright/test';
import { user } from '../stand';

test('way to code-reviev page', async ({ page }) => {
  // Главная
  await page.goto('/');

  // Авторизоваться
  await page.getByRole('link', { name: 'Вход' }).click();
  await page.getByRole('textbox', { name: 'Электронная почта' }).click();
  await page.getByRole('textbox', { name: 'Электронная почта' }).fill(user.email);
  await page.getByRole('textbox', { name: 'Пароль' }).click();
  await page.getByRole('textbox', { name: 'Пароль' }).fill(user.password);
  await page.getByRole('button', { name: 'Отправить' }).click();

  await page.getByRole('link', { name: 'Начать учиться' }).click();
  await page.getByRole('link', { name: 'Мои Решения' }).click();

  // Проверяем что мы на нужной странице
  await expect(page).toHaveURL(/\/my\/solutions$/);

  // Сделать скрин результата
  await page.screenshot({ path: './screenshots/solutions.png', fullPage: true });
});