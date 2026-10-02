<?php

namespace App\Providers;

use App;
use App\Github\Github;
use App\Github\GithubInterface;
use Github\Client;
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
    public array $bindings = [
        GithubInterface::class => Github::class,
    ];

    /**
     * Register any application services.
     *
     */
    public function register(): void
    {
        $this->app->singleton(Client::class, function (): Client {
            $githubClient = new Client();
            $githubToken = (string) config('github.connections.main.token', '');

            if ($githubToken !== '') {
                $githubClient->authenticate($githubToken, null, Client::AUTH_ACCESS_TOKEN);
            }

            return $githubClient;
        });

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
