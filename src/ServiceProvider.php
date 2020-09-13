<?php

namespace SunAsterisk\LaravelSecurityChecker;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use SunAsterisk\LaravelSecurityChecker\Console\SecurityCheckCommand;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                SecurityCheckCommand::class,
            ]);
        }
    }
}
