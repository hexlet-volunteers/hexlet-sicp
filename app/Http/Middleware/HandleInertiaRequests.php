<?php

namespace App\Http\Middleware;

use App\DTO\AuthUserData;
use App\Support\Inertia\FlashBag;
use App\Support\Navigation\NavigationBuilder;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     * @var string
     */
    protected $rootView = 'app';

    /** Группы PHP-словарей, которые получает фронтенд (ADR 0003). */
    private const array TRANSLATION_GROUPS = [
        'layout',
        'account',
        'settings',
        'activitylog',
        'admin',
        'solution',
        'progresses',
        'views',
    ];

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => fn() => $request->user() ? AuthUserData::fromModel($request->user()) : null,
            ],
            'locale' => app()->getLocale(),
            // префикс относительных ключей useTView(), страница задаёт его в Controller::inertia()
            'scope' => null,
            'translations' => fn() => array_combine(
                self::TRANSLATION_GROUPS,
                array_map(fn(string $group) => trans($group), self::TRANSLATION_GROUPS),
            ),
            'nav' => fn() => app(NavigationBuilder::class)->build(),
            // Схема ставится с сервера атрибутом на <html>, без inline-скрипта. Переключателя пока нет.
            // Для нативных <form method="post">: выход и dev-login уходят в Blade-территорию полной перезагрузкой.
            'csrfToken' => fn() => csrf_token(),
            'colorScheme' => fn() => $request->cookie('mantine-color-scheme') === 'dark' ? 'dark' : 'light',
            // ponytail: Inertia показывает одно сообщение, остальные теряются; массив — когда появятся экшены с несколькими
            'flash' => fn() => app(FlashBag::class)->pull()[0] ?? null,
        ]);
    }
}
