<?php

namespace mindtwo\NativeEnum;

use Illuminate\Support\ServiceProvider;
use mindtwo\NativeEnum\Commands\MakeEnumCommand;

class EnumServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->bootCommands();
    }

    /**
     * Boot the custom commands
     *
     * @return void
     */
    private function bootCommands()
    {
        $this->publishes([
            __DIR__.'/Commands/stubs' => $this->app->basePath('stubs')
        ], 'stubs');

        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeEnumCommand::class,
            ]);
        }
    }
}
