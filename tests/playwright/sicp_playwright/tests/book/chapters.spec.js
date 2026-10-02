import { test, expect } from '@playwright/test';

test('go to the next chapter', async ({ page }) => {
  // Открываем исходную главу (принимаются цифры от 1 до 129)
  await page.goto('/chapters/23');

  // Делаем скриншот начальной страницы
  await page.screenshot({ path: './screenshots/next_chapter_start.png', fullPage: true });

  // Кликаем по ссылке "Следующая глава"
  await page.getByRole('link', { name: 'Следующая глава' }).click();

  // Проверяем, что перешли на верную страницу (URL в chapters +1)
  await expect(page).toHaveURL(/\/chapters\/24$/);

  // Делаем скриншот конечной страницы
  await page.screenshot({ path: './screenshots/next_chapter_end_screenshot.png', fullPage: true });
});

test('go to the previous chapter', async ({ page }) => {
  // Открываем исходную главу (принимаются цифры от 1 до 129)
  await page.goto('/chapters/23');

  // Делаем скриншот начальной страницы
  await page.screenshot({ path: './screenshots/previous_chapter_start.png', fullPage: true });

  // Кликаем по ссылке "Следующая глава"
  await page.getByRole('link', { name: 'Предыдущая глава' }).click();

  // Проверяем, что перешли на верную страницу (URL в chapters -1)
  await expect(page).toHaveURL(/\/chapters\/22$/);

  // Делаем скриншот конечной страницы
  await page.screenshot({ path: './screenshots/previous_chapter_end.png', fullPage: true });
});

test('link to the chapter is available', async ({ page }) => {

  // 1. Главная
  await page.goto('/');

  // 2. Переход к оглавлению
  await page.getByRole('link', { name: 'Оглавление' }).click();

  // 3. Переход к нужной главе
  await page.getByRole('link', { name: 'Составные процедуры' }).click();

  // 4. Ожидаем открытие popup заранее
  const popupPromise = page.waitForEvent('popup');

  // 5. Кликаем по внешней ссылке (иконка)
  await page.getByRole('link', { name: '' }).click();

  // 6. Получаем popup-страницу
  const popup = await popupPromise;

  // 7. Проверяем, что popup загрузился
  await popup.waitForLoadState('domcontentloaded');

  // 8. Проверяем, что текст на внешней странице корректный
  await expect(popup.locator('body')).toContainText('1.1.4 Compound Procedures');

  // 9. Проверка URL popup-страницы
  await expect(popup).toHaveURL(
    'https://mitp-content-server.mit.edu/books/content/sectbyfn/books_pres_0/6515/sicp.zip/full-text/book/book-Z-H-10.html#%_sec_1.1.4'
  );

  // 10. Скриншот основной страницы
  await popup.screenshot({ path: './screenshots/link_to_chapter.png', fullPage: true });
});


// ВНИМАНИЕ!!! Перед запуском либо поменяй главу, либо удали и заново создай юзера
test('read the chapter', async ({ page }) => {
  await page.goto('/');

  // Выбираем главу
  await page.getByRole('link', { name: 'Оглавление' }).click();

  // Главу можно менять, если не удалил юзера
  await page.getByRole('link', { name: '1.1.2 Имена и окружение' }).click(); 

  // Логинимся
  await page.getByRole('main').getByRole('link', { name: 'Вход' }).click();
  await page.getByRole('textbox', { name: 'Электронная почта' }).fill('read@read.ru');
  await page.getByRole('textbox', { name: 'Пароль' }).fill('12345678');
  await page.getByRole('button', { name: 'Отправить' }).click();

  // Дожидаемся обновления контента после логина
  await page.waitForSelector('main', { state: 'visible' });

  // Ловим confirm() и жмём OK
  page.once('dialog', dialog => {
    console.log(`Dialog message: ${dialog.message()}`);
    dialog.accept().catch(() => {});
  });

  // Теперь ищем кнопку заново после обновления DOM
  const markRead = page.getByRole('link', { name: 'Отметить прочитанной' });
  await markRead.waitFor({ state: 'visible' });
  await markRead.click();

  // Проверяем сообщение
  await expect(page.getByText('Успешно')).toBeVisible();

  // Скриншот
  await page.screenshot({ path: './screenshots/cahpter_was_read.png', fullPage: true });
});
