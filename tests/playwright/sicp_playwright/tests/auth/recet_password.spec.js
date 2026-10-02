import { test, expect } from '@playwright/test';

test('recetting unexisted password', async ({ page }) => {
  await page.goto('/');

  await page.getByRole('link', { name: 'Вход' }).click();
  await page.getByRole('link', { name: 'Забыли пароль?' }).click();

  await page.getByRole('textbox', { name: 'Электронная почта' }).fill('none@exist.io');
  await page.getByRole('button', { name: 'Отправить' }).click();

  // Ищем локатор с уведомлением
  //await expect(page.getByRole('alert')).toBeVisible();
  await expect(page.getByRole('alert')).toHaveText("We can't find a user with that e-mail address.");

  // сделать скрин результата
  await page.screenshot({ path: './screenshots/recet-password.png', fullPage: true });
});