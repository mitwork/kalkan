<?php

namespace Mitwork\Kalkan\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kalkan:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the Kalkan components and resources';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Publish...
        $this->callSilent('vendor:publish', ['--tag' => 'kalkan-config', '--force' => true]);

        // Kalkan Provider...
        // $this->installServiceProviderAfter('RouteServiceProvider', 'KalkanServiceProvider');

        // Tests...
        // $stubs = $this->getTestStubsPath();

        // copy($stubs.'/KalkanBaseTest.php', base_path('tests/Feature/KalkanBaseTest.php'));

        return self::SUCCESS;
    }

    /**
     * Install the service provider in the application configuration file.
     *
     * @param  string  $after
     * @param  string  $name
     * @return void
     */
    protected function installServiceProviderAfter($after, $name)
    {
        if (! Str::contains($appConfig = file_get_contents(config_path('app.php')), 'App\\Providers\\'.$name.'::class')) {
            file_put_contents(config_path('app.php'), str_replace(
                'App\\Providers\\'.$after.'::class,',
                'App\\Providers\\'.$after.'::class,'.PHP_EOL.'        App\\Providers\\'.$name.'::class,',
                $appConfig
            ));
        }
    }

    /**
     * Returns the path to the correct test stubs.
     *
     * @return string
     */
    protected function getTestStubsPath()
    {
        return __DIR__.'/../../stubs/tests';
    }
}
