import { test, expect } from '@playwright/test';
import { isProduction } from '../stand';

test('email без локальной части', async ({ page }) => {
  // Регистрация
  await page.goto('/register');

  // Заполнить невалидными данными
  await page.getByRole('textbox', { name: 'Электронная почта' }).fill('@mail.ru');

  // Заполнить валидными данными
  await page.getByRole('textbox', { name: 'Имя' }).fill('Ivan');
  await page.getByRole('textbox', { name: 'Пароль' }).fill('12345678');
  await page.getByRole('textbox', { name: 'Подтверждение пароля' }).fill('12345678');

  await page.getByRole('button', { name: 'Отправить' }).click();
  await expect(page.getByText('Некорректный адрес электронной почты')).toBeVisible();

  // Сделать скрин результата
  await page.screenshot({ path: './screenshots/invalid-email-1.png', fullPage: true });
});

test('email без доменной части', async ({ page }) => {
  // Регистрация
  await page.goto('/register');

  // Заполнить невалидными данными
  await page.getByRole('textbox', { name: 'Электронная почта' }).fill('mail@');

  // Заполнить валидными данными
  await page.getByRole('textbox', { name: 'Имя' }).fill('Ivan');
  await page.getByRole('textbox', { name: 'Пароль' }).fill('12345678');
  await page.getByRole('textbox', { name: 'Подтверждение пароля' }).fill('12345678');

  await page.getByRole('button', { name: 'Отправить' }).click();
  await expect(page.getByText('Некорректный адрес электронной почты')).toBeVisible();

  // Сделать скрин результата
  await page.screenshot({ path: './screenshots/invalid-email-2.png', fullPage: true });
});

test('email с двойным @', async ({ page }) => {
  // Регистрация
  await page.goto('/register');

  // Заполнить невалидными данными
  await page.getByRole('textbox', { name: 'Электронная почта' }).fill('ma@il@mail.ru');

  // Заполнить валидными данными
  await page.getByRole('textbox', { name: 'Имя' }).fill('Ivan');
  await page.getByRole('textbox', { name: 'Пароль' }).fill('12345678');
  await page.getByRole('textbox', { name: 'Подтверждение пароля' }).fill('12345678');

  await page.getByRole('button', { name: 'Отправить' }).click();
  await expect(page.getByText('Некорректный адрес электронной почты')).toBeVisible();

  // Сделать скрин результата
  await page.screenshot({ path: './screenshots/invalid-email-3.png', fullPage: true });
});

test('email с точкой в начале локальной части', async ({ page }) => {
  // Регистрация
  await page.goto('/register');

  // Заполнить невалидными данными
  await page.getByRole('textbox', { name: 'Электронная почта' }).fill('.mail@mail.ru');

  // Заполнить валидными данными
  await page.getByRole('textbox', { name: 'Имя' }).fill('Ivan');
  await page.getByRole('textbox', { name: 'Пароль' }).fill('12345678');
  await page.getByRole('textbox', { name: 'Подтверждение пароля' }).fill('12345678');

  await page.getByRole('button', { name: 'Отправить' }).click();
  await expect(page.getByText('Некорректный адрес электронной почты')).toBeVisible();

  // Сделать скрин результата
  await page.screenshot({ path: './screenshots/invalid-email-4.png', fullPage: true });
});

test('email с точкой в конце локальной части', async ({ page }) => {
  // Регистрация
  await page.goto('/register');

  // Заполнить невалидными данными
  await page.getByRole('textbox', { name: 'Электронная почта' }).fill('mail.@mail.ru');

  // Заполнить валидными данными
  await page.getByRole('textbox', { name: 'Имя' }).fill('Ivan');
  await page.getByRole('textbox', { name: 'Пароль' }).fill('12345678');
  await page.getByRole('textbox', { name: 'Подтверждение пароля' }).fill('12345678');

  await page.getByRole('button', { name: 'Отправить' }).click();
  await expect(page.getByText('Некорректный адрес электронной почты')).toBeVisible();

  // Сделать скрин результата
  await page.screenshot({ path: './screenshots/invalid-email-5.png', fullPage: true });
});

test('email с двумя точками подряд в локальной части', async ({ page }) => {
  // Регистрация
  await page.goto('/register');

  // Заполнить невалидными данными
  await page.getByRole('textbox', { name: 'Электронная почта' }).fill('ma..il@mail.ru');

  // Заполнить валидными данными
  await page.getByRole('textbox', { name: 'Имя' }).fill('Ivan');
  await page.getByRole('textbox', { name: 'Пароль' }).fill('12345678');
  await page.getByRole('textbox', { name: 'Подтверждение пароля' }).fill('12345678');

  await page.getByRole('button', { name: 'Отправить' }).click();
  await expect(page.getByText('Некорректный адрес электронной почты')).toBeVisible();

  // Сделать скрин результата
  await page.screenshot({ path: './screenshots/invalid-email-6.png', fullPage: true });
});

test('email с двумя точками подряд в доменной части', async ({ page }) => {
  // Регистрация
  await page.goto('/register');

  // Заполнить невалидными данными
  await page.getByRole('textbox', { name: 'Электронная почта' }).fill('mail@ma..il.ru');

  // Заполнить валидными данными
  await page.getByRole('textbox', { name: 'Имя' }).fill('Ivan');
  await page.getByRole('textbox', { name: 'Пароль' }).fill('12345678');
  await page.getByRole('textbox', { name: 'Подтверждение пароля' }).fill('12345678');

  await page.getByRole('button', { name: 'Отправить' }).click();
  await expect(page.getByText('Некорректный адрес электронной почты')).toBeVisible();

  // Сделать скрин результата
  await page.screenshot({ path: './screenshots/invalid-email-7.png', fullPage: true });
});

test('email с допустимым спецсимволом', async ({ page, baseURL }) => {
  test.skip(isProduction(baseURL), 'заводит или удаляет аккаунт — не в продакшене');

  // Регистрация
  await page.goto('/register');

  // Заполнить (не)валидными данными
  await page.getByRole('textbox', { name: 'Электронная почта' }).fill('ma+il@mail.ru');

  // Заполнить валидными данными
  await page.getByRole('textbox', { name: 'Имя' }).fill('Ivan');
  await page.getByRole('textbox', { name: 'Пароль' }).fill('12345678');
  await page.getByRole('textbox', { name: 'Подтверждение пароля' }).fill('12345678');

  await page.getByRole('button', { name: 'Отправить' }).click();
  await expect(page.getByText('На вашу электронную почту')).toBeVisible();

  // Сделать скрин результата
  await page.screenshot({ path: './screenshots/invalid-email-8.png', fullPage: true });
});

test('email с недопустимыми спецсимволами', async ({ page }) => {
  // Регистрация
  await page.goto('/register');

  // Заполнить невалидными данными
  await page.getByRole('textbox', { name: 'Электронная почта' }).fill('ma()il@mail.ru');

  // Заполнить валидными данными
  await page.getByRole('textbox', { name: 'Имя' }).fill('Ivan');
  await page.getByRole('textbox', { name: 'Пароль' }).fill('12345678');
  await page.getByRole('textbox', { name: 'Подтверждение пароля' }).fill('12345678');

  await page.getByRole('button', { name: 'Отправить' }).click();
  await expect(page.getByText('Некорректный адрес электронной почты')).toBeVisible();

  // Сделать скрин результата
  await page.screenshot({ path: './screenshots/invalid-email-9.png', fullPage: true });
});

test('email без точки в домене', async ({ page }) => {
  // Регистрация
  await page.goto('/register');

  // Заполнить невалидными данными
  await page.getByRole('textbox', { name: 'Электронная почта' }).fill('mail@mail');

  // Заполнить валидными данными
  await page.getByRole('textbox', { name: 'Имя' }).fill('Ivan');
  await page.getByRole('textbox', { name: 'Пароль' }).fill('12345678');
  await page.getByRole('textbox', { name: 'Подтверждение пароля' }).fill('12345678');

  await page.getByRole('button', { name: 'Отправить' }).click();
  await expect(page.getByText('Некорректный адрес электронной почты')).toBeVisible();

  // Сделать скрин результата
  await page.screenshot({ path: './screenshots/invalid-email-10.png', fullPage: true });
});

test('очень длинный email (100 локаль, 100 домен и .ru', async ({ page }) => {
  // Регистрация
  await page.goto('/register');

  // Заполнить невалидными данными
  await page.getByRole('textbox', { name: 'Электронная почта' }).fill('bfdhsiuwqhiurdhidufshhlfdxhfhkcxvhjbhzxbkhjvchdfblhlisadfvlzdfaisdlfbgldkzufgladfukighladkfuigldzfug@bfdhsiuwqhiurdhidufshhlfdxhfhkcxvhjbhzxbkhjvchdfblhlisadfvlzdfaisdlfbgldkzufgladfukighladkfuigldzfug.ru');

  // Заполнить валидными данными
  await page.getByRole('textbox', { name: 'Имя' }).fill('Ivan');
  await page.getByRole('textbox', { name: 'Пароль' }).fill('12345678');
  await page.getByRole('textbox', { name: 'Подтверждение пароля' }).fill('12345678');

  await page.getByRole('button', { name: 'Отправить' }).click();
  await expect(page.getByText('Некорректный адрес электронной почты')).toBeVisible();

  // Сделать скрин результата
  await page.screenshot({ path: './screenshots/invalid-email-11.png', fullPage: true });
});

test('email с кириллицей', async ({ page }) => {
  // Регистрация
  await page.goto('/register');

  // Заполнить невалидными данными
  await page.getByRole('textbox', { name: 'Электронная почта' }).fill('почта@почта.рф');

  // Заполнить валидными данными
  await page.getByRole('textbox', { name: 'Имя' }).fill('Ivan');
  await page.getByRole('textbox', { name: 'Пароль' }).fill('12345678');
  await page.getByRole('textbox', { name: 'Подтверждение пароля' }).fill('12345678');

  await page.getByRole('button', { name: 'Отправить' }).click();
  await expect(page.getByText('Некорректный адрес электронной почты')).toBeVisible();

  // Сделать скрин результата
  await page.screenshot({ path: './screenshots/invalid-email-12.png', fullPage: true });
});

test('email с пробелом', async ({ page }) => {
  // Регистрация
  await page.goto('/register');

  // Заполнить невалидными данными
  await page.getByRole('textbox', { name: 'Электронная почта' }).fill('ma il@mail.ru');

  // Заполнить валидными данными
  await page.getByRole('textbox', { name: 'Имя' }).fill('Ivan');
  await page.getByRole('textbox', { name: 'Пароль' }).fill('12345678');
  await page.getByRole('textbox', { name: 'Подтверждение пароля' }).fill('12345678');

  await page.getByRole('button', { name: 'Отправить' }).click();
  await expect(page.getByText('Некорректный адрес электронной почты')).toBeVisible();

  // Сделать скрин результата
  await page.screenshot({ path: './screenshots/invalid-email-13.png', fullPage: true });
});

test('email с пробелом в начале и в конце', async ({ page, baseURL }) => {
  test.skip(isProduction(baseURL), 'заводит или удаляет аккаунт — не в продакшене');

  // Регистрация
  await page.goto('/register');

  // Заполнить (не)валидными данными
  await page.getByRole('textbox', { name: 'Электронная почта' }).fill(' mail@mail.ru ');

  // Заполнить валидными данными
  await page.getByRole('textbox', { name: 'Имя' }).fill('Ivan');
  await page.getByRole('textbox', { name: 'Пароль' }).fill('12345678');
  await page.getByRole('textbox', { name: 'Подтверждение пароля' }).fill('12345678');

  await page.getByRole('button', { name: 'Отправить' }).click();
  await expect(page.getByText('На вашу электронную почту')).toBeVisible();

  // Сделать скрин результата
  await page.screenshot({ path: './screenshots/invalid-email-14.png', fullPage: true });
});

test('пустая строка', async ({ page }) => {
  // Регистрация
  await page.goto('/register');

  // Заполнить невалидными данными
  await page.getByRole('textbox', { name: 'Электронная почта' }).fill('');

  // Заполнить валидными данными
  await page.getByRole('textbox', { name: 'Имя' }).fill('Ivan');
  await page.getByRole('textbox', { name: 'Пароль' }).fill('12345678');
  await page.getByRole('textbox', { name: 'Подтверждение пароля' }).fill('12345678');

  await page.getByRole('button', { name: 'Отправить' }).click();
  await expect(page.getByText('Введите адрес электронной почты')).toBeVisible();

  // Сделать скрин результата
  await page.screenshot({ path: './screenshots/invalid-email-15.png', fullPage: true });
});

test('Удалить пользователя с + почтой', async ({ page, baseURL }) => {
  test.skip(isProduction(baseURL), 'заводит или удаляет аккаунт — не в продакшене');

  await page.goto('/');

  await page.getByRole('link', { name: 'Вход' }).click();
  await page.getByRole('textbox', { name: 'Электронная почта' }).fill('ma+il@mail.ru');
  await page.getByRole('textbox', { name: 'Пароль' }).fill('12345678');
  await page.getByRole('button', { name: 'Отправить' }).click();

  await page.locator('#dropdownMenuButton').click();
  await expect(page.getByRole('link', { name: 'Ivan' })).toBeVisible();


  await page.getByRole('link', { name: 'Ivan' }).click();
  await page.getByRole('link', { name: 'Редактировать профиль' }).click();
  await page.getByRole('link', { name: 'Аккаунт' }).click();

  page.once('dialog', async dialog => {
  expect(dialog.message()).toBe('Вы уверены, что хотите удалить свой аккаунт ?');
  await dialog.accept();
  });

  await page.getByRole('link', { name: 'Удалить Аккаунт' }).click();

  await expect(page.getByText('Ваш аккаунт успешно удален!')).toBeVisible();
});

test('Удалить пользователя с пробелами в почте', async ({ page, baseURL }) => {
  test.skip(isProduction(baseURL), 'заводит или удаляет аккаунт — не в продакшене');

  await page.goto('/');

  await page.getByRole('link', { name: 'Вход' }).click();
  await page.getByRole('textbox', { name: 'Электронная почта' }).fill('mail@mail.ru');
  await page.getByRole('textbox', { name: 'Пароль' }).fill('12345678');
  await page.getByRole('button', { name: 'Отправить' }).click();

  await page.locator('#dropdownMenuButton').click();
  await expect(page.getByRole('link', { name: 'Ivan' })).toBeVisible();


  await page.getByRole('link', { name: 'Ivan' }).click();
  await page.getByRole('link', { name: 'Редактировать профиль' }).click();
  await page.getByRole('link', { name: 'Аккаунт' }).click();

  page.once('dialog', async dialog => {
  expect(dialog.message()).toBe('Вы уверены, что хотите удалить свой аккаунт ?');
  await dialog.accept();
  });

  await page.getByRole('link', { name: 'Удалить Аккаунт' }).click();

  await expect(page.getByText('Ваш аккаунт успешно удален!')).toBeVisible();
});
