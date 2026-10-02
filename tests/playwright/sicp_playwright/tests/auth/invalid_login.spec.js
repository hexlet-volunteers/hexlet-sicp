import { test, expect } from '@playwright/test';
import { user } from '../stand';

test('invalid login (invalid mail + password)', async ({ page }) => {
  // Открыть Главную
  await page.goto('/');

  // Залогиниться
  await page.getByRole('link', { name: 'Вход' }).click();
  await page.getByRole('textbox', { name: 'Электронная почта' }).fill('mail@mail.ru');
  await page.getByRole('textbox', { name: 'Пароль' }).fill('password');
  await page.getByRole('button', { name: 'Отправить' }).click();

  // Увидеть уведомление о неуспешном входе
  await expect(page.getByText('Эти учетные данные не соответствуют нашим записям')).toBeVisible();

  // Делаем скриншот конечной страницы
  await page.screenshot({ path: './screenshots/invalid_login_1.png', fullPage: true });
});


test('invalid login (valid mail + invalid password)', async ({ page }) => {
  // Открыть Главную
  await page.goto('/');

  // Залогиниться
  await page.getByRole('link', { name: 'Вход' }).click();
  await page.getByRole('textbox', { name: 'Электронная почта' }).fill(user.email);
  await page.getByRole('textbox', { name: 'Пароль' }).fill(`${user.password}-wrong`);
  await page.getByRole('button', { name: 'Отправить' }).click();

  // Увидеть уведомление о неуспешном входе
  await expect(page.getByText('Эти учетные данные не соответствуют нашим записям')).toBeVisible();

  // Делаем скриншот конечной страницы
  await page.screenshot({ path: './screenshots/invalid_login_2.png', fullPage: true });
});