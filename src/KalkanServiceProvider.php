<?php

namespace Mitwork\Kalkan;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class KalkanServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services...
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/kalkan.php' => config_path('kalkan.php'),
        ]);

    }
}
