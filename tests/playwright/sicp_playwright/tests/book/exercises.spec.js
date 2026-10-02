import { test, expect } from '@playwright/test';
import { user } from '../stand';

test('go to the next exercise', async ({ page }) => {
  // Открываем исходное упражнение (принимаются цифры от 1 до 356)
  await page.goto('/exercises/1');

  // Делаем скриншот начальной страницы
  await page.screenshot({ path: './screenshots/next_exercise_start.png', fullPage: true });

  // Кликаем по ссылке "Следующее"
  await page.getByRole('link', { name: 'Следующее' }).click();

  // Проверяем, что перешли на верную страницу
  await expect(page).toHaveURL(/\/exercises\/2$/);

  // Делаем скриншот конечной страницы
  await page.screenshot({ path: './screenshots/next_exercise_end_screenshot.png', fullPage: true });
});

test('go to the previous exercise', async ({ page }) => {
  // Открываем исходное упражнение (принимаются цифры от 1 до 356)
  await page.goto('/exercises/2');

  // Делаем скриншот начальной страницы
  await page.screenshot({ path: './screenshots/previous_exercise_start.png', fullPage: true });

  // Кликаем по ссылке "Предыдущее"
  await page.getByRole('link', { name: 'Предыдущее' }).click();

  // Проверяем, что перешли на верную страницу
  await expect(page).toHaveURL(/\/exercises\/1$/);

  // Делаем скриншот конечной страницы
  await page.screenshot({ path: './screenshots/previous_exercise_end.png', fullPage: true });
});


test('exercises sub chapter availability', async ({ page }) => {
  await page.goto('/');

  // Переходим в Упражнения
  await page.getByRole('link', { name: 'Упражнения' }).click();

  // Открыть раздел 2
  await page.getByRole('tab', { name: '2' }).click();
  await expect(page.getByText('Модификация make-rat')).toBeVisible({ timeout: 10000 });

  // Делаем промежуточный скриншот
  await page.screenshot({ path: './screenshots/exercise_2.png', fullPage: true });

  // Открыть раздел 3
  await page.getByRole('tab', { name: '3' }).click();
  await expect(page.getByText('Создание накопителей')).toBeVisible({ timeout: 10000 });

  // Делаем промежуточный скриншот
  await page.screenshot({ path: './screenshots/exercise_3.png', fullPage: true });

  // Открыть раздел 4
  await page.getByRole('tab', { name: 'Метаязыковая абстракция' }).click();
  await expect(page.getByText('4.1 Порядок вычисления')).toBeVisible({ timeout: 10000 });

  // Делаем промежуточный скриншот
  await page.screenshot({ path: './screenshots/exercise_4.png', fullPage: true });

  // Открыть раздел 5
  await page.getByRole('tab', { name: '5' }).click();
  await expect(page.getByText('5.1 Регистровая машина')).toBeVisible({ timeout: 10000 });

  // Делаем конечный скриншот
  await page.screenshot({ path: './screenshots/exercise_5.png', fullPage: true });
});

test('see those who solwed the exercise', async ({ page }) => {
  // Начало с Главной
  await page.goto('/');

  await page.getByRole('link', { name: 'Упражнения' }).click();
  await page.getByRole('link', { name: 'Последовательность выражений' }).click();
  await page.getByRole('link', { name: 'Вход' }).click();

  // Логинимся
  await page.getByRole('textbox', { name: 'Электронная почта' }).fill(user.email);
  await page.getByRole('textbox', { name: 'Пароль' }).fill(user.password);
  await page.getByRole('button', { name: 'Отправить' }).click();

  // Кликаем по тексту
  await page.getByRole('button', { name: 'Показать кто завершил' }).click();

  // Ищем по заголовку
  await expect(page.locator('#completed-by-title')).toContainText('Завершено пользователями:');

  // Ждём, пока модалка полностью появится
  await page.waitForTimeout(300); 

  // Скриншот
  await page.screenshot({ path: './screenshots/those_who_solved.png', fullPage: true });
});