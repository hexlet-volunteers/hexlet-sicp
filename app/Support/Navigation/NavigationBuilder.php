<?php

namespace App\Support\Navigation;

use App\DTO\Navigation\LocaleLinkData;
use App\DTO\Navigation\NavigationData;
use App\DTO\Navigation\NavItemData;
use App\DTO\Navigation\NavSectionData;
use App\Helpers\LocalizationHelper;
use App\Helpers\TemplateHelper;
use App\Models\User;
use Illuminate\Http\Request;
use LaravelLocalization;

/**
 * Состав шапки и футера. Рендерят его Inertia-шелл (shared prop `nav`)
 * и Blade-лейаут (_nav.blade.php и _footer.blade.php через ViewServiceProvider).
 */
class NavigationBuilder
{
    /** Маршруты, уже переехавшие на Inertia: на них ведёт <Link>, на остальные — <a>. */
    private const array INERTIA_ROUTES = [
        'settings.profile.index',
        'settings.account.index',
        'log.index',
        'admin.users.index',
        'solutions.show',
        'users.solutions.show',
        'admin.users.edit',
        'admin.export.index',
        'my.show',
        'admin.comments.index',
        'admin.solutions.index',
        'solutions.index',
        'my.solutions.index',
    ];

    public function __construct(private Request $request)
    {
    }

    public function build(): NavigationData
    {
        $locale = app()->getLocale();
        /** @var User|null $user */
        $user = $this->request->user();

        return new NavigationData(
            homeUrl: LaravelLocalization::getLocalizedURL($locale, route('home')),
            logoUrl: asset("images/logo-{$locale}.svg"),
            logoAlt: __('layout.nav.logo_alt'),
            main: $this->main($locale, $user),
            user: $user === null ? $this->guest() : $this->userMenu($user),
            currentLocale: $this->localeLink($locale),
            otherLocales: array_values(array_map(
                fn(string $code) => $this->localeLink($code),
                array_keys(LocalizationHelper::getOtherLocales($locale, LaravelLocalization::getSupportedLocales())),
            )),
            footer: $this->footer($locale),
        );
    }

    /**
     * @return array<int, NavItemData>
     */
    public function settings(): array
    {
        return [
            $this->route('account.profile', 'settings.profile.index'),
            $this->route('account.account', 'settings.account.index'),
        ];
    }

    /**
     * Меню админки. Боковое меню админской страницы передаёт её фильтр, чтобы
     * пользователя, найденного в одном списке, искать и в других. В шапку фильтр
     * не попадает: на публичных страницах у него другие поля.
     *
     * @param array<string, array<string, string|null>> $filter
     * @return array<int, NavItemData>
     */
    public function admin(array $filter = []): array
    {
        return [
            $this->route('admin.users.title', 'admin.users.index', 'users', $filter, 'admin.users.*'),
            $this->route('admin.comments.title', 'admin.comments.index', 'messages', $filter, 'admin.comments.*'),
            $this->route('admin.solutions.title', 'admin.solutions.index', 'code', $filter, 'admin.solutions.*'),
            $this->route('admin.export.title', 'admin.export.index', 'download', $filter, 'admin.export.*'),
        ];
    }

    /**
     * Вкладки «Упражнения / Решения». Blade-версия — exercise/navigation.blade.php, её ещё рендерит exercise/index.
     *
     * @return array<int, NavItemData>
     */
    public function exercises(): array
    {
        return [
            $this->route('layout.nav.exercises', 'exercises.index'),
            $this->route('views.solution.index.header.h1', 'solutions.index'),
        ];
    }

    /**
     * @return array<int, NavItemData>
     */
    private function main(string $locale, ?User $user): array
    {
        $items = [
            $this->route('layout.nav.chapters', 'chapters.index'),
            $this->route('layout.nav.exercises', 'exercises.index'),
            // TODO: translate guide to english
            $locale === 'ru'
                ? $this->link('layout.nav.sicp_read', 'https://guides.hexlet.io/how-to-learn-sicp/')
                : $this->link('layout.nav.sicp_read', route('pages.show', ['page' => 'how-to-learn-sicp'])),
            $this->route('layout.nav.rating', 'top.index'),
            new NavItemData(
                label: __('layout.nav.sicp_book'),
                href: TemplateHelper::getBookLink($locale),
                highlight: true,
            ),
        ];

        if ($user?->can('accessAdmin', User::class)) {
            $items[] = new NavItemData(
                label: __('admin.title'),
                href: route('admin.users.index'),
                icon: 'shield-lock',
                children: $this->admin(),
            );
        }

        return $items;
    }

    /**
     * @return array<int, NavItemData>
     */
    private function guest(): array
    {
        $items = [];

        if (app()->environment('local')) {
            $items[] = new NavItemData(label: 'Dev-login', href: route('auth.dev-login'), method: 'post');
        }

        $items[] = $this->route('layout.nav.login', 'login');
        $items[] = $this->route('layout.nav.register', 'register');

        return $items;
    }

    /**
     * @return array<int, NavItemData>
     */
    private function userMenu(User $user): array
    {
        return [
            new NavItemData(label: $user->name, href: route('users.show', $user)),
            $this->route('account.settings', 'settings.account.index'),
            $this->route('layout.nav.my_progress', 'my.show'),
            new NavItemData(label: __('layout.nav.logout'), href: route('logout'), method: 'post'),
        ];
    }

    /**
     * @return array<int, NavSectionData>
     */
    private function footer(string $locale): array
    {
        $projects = [
            $this->link('layout.footer.os_projects.cv', 'https://github.com/Hexlet/hexlet-cv'),
            $this->link('layout.footer.os_projects.editor', 'https://github.com/hexlet-rus/runit'),
        ];

        if ($locale === 'ru') {
            $projects[] = $this->link('layout.footer.os_projects.career', 'https://career.hexlet.io/');
        }

        return [
            new NavSectionData(null, [
                $this->link('layout.footer.about', route('pages.show', ['page' => 'about'])),
                $this->link('layout.footer.source_code', 'https://github.com/Hexlet/hexlet-sicp'),
                $this->link('layout.footer.volunteers_in_tg', 'https://t.me/hexletcommunity/12'),
            ]),
            new NavSectionData(__('layout.footer.help'), [
                $this->link('layout.footer.free', 'https://ru.hexlet.io/courses_free'),
                $this->link('layout.footer.recommended_books', 'https://ru.hexlet.io/pages/recommended-books'),
            ]),
            new NavSectionData(__('layout.footer.other_os_projects'), $projects),
            new NavSectionData(__('layout.footer.additionally'), [
                $this->link('layout.footer.os_projects.hexlet', 'https://ru.hexlet.io/'),
                $this->link('layout.footer.os_projects.code_basics', 'https://ru.code-basics.com/'),
                $this->link('layout.footer.os_projects.codebattle', 'https://codebattle.hexlet.io/'),
            ]),
        ];
    }

    /**
     * @param array<string, array<string, string|null>> $parameters
     * @param string|null $activePattern шаблон routeIs(), если пункт подсвечивается и на вложенных страницах
     */
    private function route(
        string $labelKey,
        string $routeName,
        ?string $icon = null,
        array $parameters = [],
        ?string $activePattern = null,
    ): NavItemData {
        return new NavItemData(
            label: __($labelKey),
            href: route($routeName, $parameters),
            active: $this->request->routeIs($activePattern ?? $routeName),
            inertia: in_array($routeName, self::INERTIA_ROUTES, true),
            icon: $icon,
        );
    }

    private function link(string $labelKey, string $href): NavItemData
    {
        return new NavItemData(label: __($labelKey), href: $href);
    }

    private function localeLink(string $code): LocaleLinkData
    {
        return new LocaleLinkData(
            code: $code,
            label: LocalizationHelper::getNativeLanguageName($code),
            flagUrl: LocalizationHelper::getPathToLocaleFlag($code),
            href: LaravelLocalization::getLocalizedURL($code, null, [], true),
        );
    }
}
