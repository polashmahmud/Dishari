<?php

namespace Polashmahmud\Menu;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Polashmahmud\Menu\Commands\InstallDishari;
use Polashmahmud\Menu\Models\MenuGroup;

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
            Inertia::share('dishari', fn() => $this->resolveMenuData());
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
            __DIR__ . '/../resources/js/pages' => resource_path("js/pages/{$dirName}"),
            __DIR__ . '/../resources/js/components' => resource_path("js/components/{$dirName}"),
        ], 'dishari-views');

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallDishari::class,
            ]);
        }
    }

    /**
     * Resolve the menu data based on auth and cache config.
     */
    protected function resolveMenuData()
    {
        // 1. check authentication requirement
        if (config('dishari.auth_required', true) && !Auth::check()) {
            return [];
        }

        // 2. if cache is disabled, return data directly (Early Return)
        if (!config('dishari.cache.enabled', true)) {
            return MenuGroup::getTree();
        }

        // 3. return data from cache
        return Cache::remember(
            config('dishari.cache.key', 'dishari_sidebar_menu'),
            config('dishari.cache.ttl', 3600),
            fn() => MenuGroup::getTree()
        );
    }
}
