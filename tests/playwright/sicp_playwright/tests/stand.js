// Учётка, под которой спеки логинятся. По умолчанию — тестовый пользователь продакшена;
// на стенде подставить существующего, например из сидов: admin@test.com / password.
export const user = {
  email: process.env.PLAYWRIGHT_USER_EMAIL ?? 'test@test.com',
  password: process.env.PLAYWRIGHT_USER_PASSWORD ?? '12345678',
};

// Спеки, которые заводят и удаляют аккаунты, в продакшене не запускаем.
export const isProduction = (baseURL) => new URL(baseURL).hostname === 'sicp.hexlet.io';
