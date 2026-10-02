import { test, expect } from '@playwright/test';
import { isProduction } from '../stand';

test('register end delete user', async ({ page, baseURL }) => {
  test.skip(isProduction(baseURL), 'заводит или удаляет аккаунт — не в продакшене');

  await page.goto('/');

  // Регистрируем тестового пользователя
  await page.getByRole('link', { name: 'Регистрация' }).click();
  await page.getByRole('textbox', { name: 'Электронная почта' }).fill('del@del.ru');
  await page.getByRole('textbox', { name: 'Имя' }).fill('Делитович');
  await page.getByRole('textbox', { name: 'Пароль' }).fill('12345678');
  await page.getByRole('textbox', { name: 'Подтверждение пароля' }).fill('12345678');
  await page.getByRole('button', { name: 'Отправить' }).click();


  await expect(page.getByText('На вашу электронную почту отправлена ссылка для подтверждения регистрации')).toBeVisible();
  
  // Делаем скриншот конечной страницы
  await page.screenshot({ path: './screenshots/user_was_registered.png', fullPage: true });

  // Удаляем пользователя
  await page.getByRole('link', { name: 'Делитович' }).click();
  await page.getByRole('link', { name: 'Редактировать профиль' }).click();
  await page.getByRole('link', { name: 'Аккаунт' }).click();

  // Ловим confirm() и жмём OK
  page.once('dialog', dialog => {
    console.log(`Dialog message: ${dialog.message()}`);
    dialog.accept().catch(() => {});
  });

  // Кликаем "Удалить Аккаунт"
  await page.getByRole('link', { name: 'Удалить Аккаунт' }).click();

  // Ждём появления подтверждения
  await expect(page.getByText('Ваш аккаунт успешно удален!')).toBeVisible();

  // Скриншот
  await page.screenshot({ path: './screenshots/user_was_deleted.png', fullPage: true });
});