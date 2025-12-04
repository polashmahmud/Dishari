<?php

namespace Polashmahmud\Menu;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class MenuProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/menu.php', 'menu');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // Load routes with web middleware group
        Route::middleware('web')
            ->group(__DIR__ . '/../../routes/web.php');
    }
}
