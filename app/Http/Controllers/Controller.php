<?php

namespace App\Http\Controllers;

use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    /**
     * @param string|null $scope префикс ключей переводов страницы для useTView(); задаётся явно,
     *     потому что legacy-словари не совпадают с именами контроллеров
     */
    protected function inertia(array $props = [], ?string $view = null, ?string $scope = null): Response
    {
        if ($scope !== null) {
            Inertia::share('scope', $scope);
        }

        if (class_exists(Debugbar::class)) {
            Debugbar::addMessage(Inertia::getShared(), 'sharedProps');
            Debugbar::addMessage($props, 'pageProps');
        }

        if ($view) {
            return Inertia::render($view, $props);
        }

        $action = request()->route()->getActionName();
        $action = Str::replaceFirst('App\\Http\\Controllers\\', '', $action);
        $action = Str::replaceLast('Controller@', '\\', $action);
        $parts = explode('\\', $action);

        $lastPart = array_pop($parts);
        $lastPart = Str::studly($lastPart);
        $parts[] = $lastPart;

        $result = implode('/', $parts);

        return Inertia::render($result, $props);
    }
}
