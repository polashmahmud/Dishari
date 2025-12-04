<?php

namespace Polashmahmud\Menu;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Polashmahmud\Menu\Commands\InstallDishari;

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
        $this->mergeConfigFrom(__DIR__ . '/../config/menu.php', 'dishari');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

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
