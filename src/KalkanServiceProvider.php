<?php

namespace Mitwork\Kalkan;

use Illuminate\Support\ServiceProvider;

class KalkanServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/kalkan.php', 'kalkan');
    }

    /**
     * Bootstrap the application services...
     */
    public function boot(): void
    {
        $this->configurePublishing();
    }

    /**
     * Configure publishing for the package.
     *
     * @return void
     */
    protected function configurePublishing()
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/kalkan.php' => config_path('kalkan.php'),
        ], 'kalkan-config');
    }
}
