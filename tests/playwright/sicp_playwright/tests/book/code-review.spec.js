import { test, expect } from '@playwright/test';
import { user } from '../stand';

test('way to code-reviev page', async ({ page }) => {
  // Главная
  await page.goto('/');

  // авторизоваться
  await page.getByRole('link', { name: 'Вход' }).click();
  await page.getByRole('textbox', { name: 'Электронная почта' }).fill(user.email);
  await page.getByRole('textbox', { name: 'Пароль' }).fill(user.password);
  await page.getByRole('button', { name: 'Отправить' }).click();

  await page.getByRole('link', { name: 'Начать учиться' }).click();
  await page.getByRole('link', { name: 'Мои Решения' }).click();
  await page.getByRole('link', { name: 'Подробнее' }).click();

  // Проверяем наличие текста "Код Ревью"
  const codeReviewLocator = page.getByText('Код Ревью');
  await expect(codeReviewLocator).toBeVisible();

  // сделать скрин результата
  await page.screenshot({ path: './screenshots/code-review.png', fullPage: true });
});