<?php

namespace Mitwork\Kalkan;

use Illuminate\Support\ServiceProvider;
use Mitwork\Kalkan\Console\InstallCommand;

class KalkanServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
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
    }

    /**
     * Configure publishing for the package.
     *
     * @return void
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
     *
     * @return void
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
}
