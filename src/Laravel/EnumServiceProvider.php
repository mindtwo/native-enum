<?php

namespace mindtwo\NativeEnum\Laravel;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class EnumServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../stubs/config/enums.php' => config_path('enums.php'),
        ]);

        $this->loadViewsFrom(__DIR__.'/../stubs/components', 'm2-enums');
    }
}
