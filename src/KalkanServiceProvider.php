<?php

namespace Mitwork\Kalkan;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Mitwork\Kalkan\Console\InstallCommand;

class KalkanServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/kalkan.php', 'kalkan');
    }

    /**
     * Bootstrap the application services...
     */
    public function boot(): void
    {
        $this->configurePublishing();
        $this->configureCommands();
        $this->configureRoutes();
    }

    /**
     * Configure publishing for the package.
     */
    protected function configurePublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/kalkan.php' => config_path('kalkan.php'),
        ], 'kalkan-config');
    }

    /**
     * Configure the commands offered by the application.
     */
    protected function configureCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            InstallCommand::class,
        ]);
    }

    /**
     * Configure test routes
     */
    protected function configureRoutes(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        Route::group([
            'namespace' => 'Mitwork\Kalkan\Http\Controllers',
            'domain' => config('kalkan.domain', null),
            'prefix' => config('kalkan.prefix', config('kalkan.path')),
        ], function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        });
    }
}
