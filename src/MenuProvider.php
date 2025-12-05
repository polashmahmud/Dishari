<?php

namespace Polashmahmud\Menu;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Polashmahmud\Menu\Commands\InstallDishari;
use Polashmahmud\Menu\Models\Menu;

class MenuProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/menu.php',
            'dishari'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (config('dishari.auto_share', true)) {
            Inertia::share('dishari', function () {
                if (config('dishari.cache.enabled', true)) {
                    $cacheKey = config('dishari.cache.key', 'dishari_sidebar_menu');
                    $ttl = config('dishari.cache.ttl', 3600);

                    return Cache::remember($cacheKey, $ttl, fn() => Menu::getTree());
                }

                return Menu::getTree();
            });
        }

        $this->mergeConfigFrom(__DIR__ . '/../config/menu.php', 'dishari');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'dishari-migrations');

        // Load routes with web middleware group
        Route::middleware('web')
            ->group(__DIR__ . '/../routes/web.php');

        // 1. Publish config file
        $this->publishes([
            __DIR__ . '/../config/menu.php' => config_path('dishari.php'),
        ], 'dishari-config');

        // 2. Get folder name from config (default is 'Menu')
        $dirName = config('dishari.directory_name', 'dishari');

        // 3. Set dynamic path
        $this->publishes([
            __DIR__ . '/../resources/js/Pages' => resource_path("js/Pages/{$dirName}"),
        ], 'dishari-views');

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallDishari::class,
            ]);
        }
    }
}
