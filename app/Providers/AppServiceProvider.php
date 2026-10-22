<?php

namespace App\Providers;

use App;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use URL;
use DB;
use Blade;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Yandex\Provider as YandexProvider;
use Spatie\LaravelTypeScriptTransformer\TypeScriptTransformerApplicationServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     */
    public function register(): void
    {
        if (class_exists(TypeScriptTransformerApplicationServiceProvider::class)) {
            $this->app->register(TypeScriptTransformerServiceProvider::class);
        }

        if (config('logging.log_sql_queries')) {
            DB::listen(function ($query): void {
                    info($query->sql, [
                            'bind' => $query->bindings,
                            'time' => $query->time,
                        ]);
            });
        }
    }

    /**
     * Bootstrap any application services.
     *
     */
    public function boot(): void
    {
        Model::preventLazyLoading(
            !App::isProduction()
        );
        Paginator::useBootstrap();
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        Blade::include('components.comments', 'comments');
        Blade::include('components.solutions', 'solutions');
        Blade::include("components.hreflang_tags", 'hreflang_tags');

        Event::listen(function (SocialiteWasCalled $event) {
            $event->extendSocialite('yandex', YandexProvider::class);
        });
    }
}
