import { test, expect } from '@playwright/test';
import { user } from '../stand';

test('chapters add new comment', async ({ page }) => {
  // Открыть страницу (выбери любую)
  await page.goto('/chapters/8');

  // Залогиниться
  await page.getByRole('main').getByRole('link', { name: 'Вход' }).click();
  await page.getByRole('textbox', { name: 'Электронная почта' }).fill(user.email);
  await page.getByRole('textbox', { name: 'Пароль' }).fill(user.password);
  await page.getByRole('button', { name: 'Отправить' }).click();

  // Найти текстовое поле по тексту-подсказке
  const commentField = page.locator('textarea:visible').first();

  // Написать тестовый комментарий
  await commentField.fill('my playwright test comment');

  // Отправить
  await page.getByRole('button', { name: 'Отправить' }).click();

  // Скрин результата
  await page.screenshot({ path: './screenshots/chapters_add_new_comment.png', fullPage: true });
});


test('delete the comment', async ({ page }) => {
  // Переходим на страницу главы
  await page.goto('/chapters/8');

  // Логинимся
  await page.getByRole('main').getByRole('link', { name: 'Вход' }).click();
  await page.getByRole('textbox', { name: 'Электронная почта' }).fill(user.email);
  await page.getByRole('textbox', { name: 'Пароль' }).fill(user.password);
  await page.getByRole('button', { name: 'Отправить' }).click();

  // Ждём, пока комментарии прогрузятся
  await page.waitForTimeout(1000); // небольшая задержка для полной загрузки (можно заменить на waitForSelector)

  // Ищем комментарий по тексту + если он не один, выбираем первый по индексу
  const comment = page.getByText('my playwright test comment').nth(0);

  const deleteButton = comment
    .locator('xpath=ancestor::*[1]/following-sibling::*//a[contains(text(), "Удалить")]');

  await expect(deleteButton).toBeVisible();

  // Ловим модальное окно и принимаем
  page.once('dialog', dialog => {
    console.log(`Dialog message: ${dialog.message()}`);
    dialog.accept().catch(() => {});
  });

  // Кликаем кнопку "Удалить"
  await deleteButton.click();

  // Ждём уведомление об успешном удалении
  await expect(page.getByText('Успешно')).toBeVisible();

  // Скрин результата
  await page.screenshot({
    path: './screenshots/comment_deleted.png',
    fullPage: true
  });
});
