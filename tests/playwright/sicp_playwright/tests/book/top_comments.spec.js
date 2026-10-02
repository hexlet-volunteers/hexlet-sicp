import { test, expect } from '@playwright/test';

test('from main to comments top', async ({ page }) => {
  // Открыть Главную
  await page.goto('/');

  // Пройти по ссылкам
  await page.getByRole('link', { name: 'Рейтинг' }).click();
  await page.getByRole('link', { name: 'Вклад в сообщество' }).click();

  // Проверяем, что перешли на верную страницу
  await expect(page).toHaveURL(/\/ratings\/comments_top$/);

  // Делаем скриншот конечной страницы
  await page.screenshot({ path: './screenshots/raitings_top.png', fullPage: true });
});