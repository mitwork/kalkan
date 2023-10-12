<?php

namespace Mitwork\Kalkan;

use Illuminate\Support\ServiceProvider;

class KalkanServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services...
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/kalkan.php' => config_path('kalkan.php'),
        ]);

    }
}
