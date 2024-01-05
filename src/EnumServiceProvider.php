<?php

namespace mindtwo\NativeEnum;

use Illuminate\Support\ServiceProvider;
use mindtwo\NativeEnum\Commands\MakeEnumCommand;
use mindtwo\NativeEnum\Rules\Enum;
use mindtwo\NativeEnum\Rules\EnumName;
use mindtwo\NativeEnum\Rules\EnumValue;

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
        $this->bootValidationTranslation();
        $this->bootValidators();
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

        $this->publishes([
            __DIR__.'/stubs/config/enums.php' => config_path('enums.php'),
        ]);

        $this->loadViewsFrom(__DIR__.'/stubs/views', 'm2-enums');
    }

    /**
     * Boot the custom validators
     *
     * @return void
     */
    private function bootValidators()
    {
        $this->app['validator']->extend('enum_name', function ($attribute, $value, $parameters, $validator) {
            $enum = $parameters[0] ?? null;

            return (new EnumName($enum))->passes($attribute, $value);
        }, __('native-enum::messages.enum_name'));

        $this->app['validator']->extend('enum_value', function ($attribute, $value, $parameters, $validator) {
            $enum = $parameters[0] ?? null;

            $strict = $parameters[1] ?? null;

            if (! $strict) {
                return (new EnumValue($enum))->passes($attribute, $value);
            }

            $strict = !! json_decode(strtolower($strict));

            return (new EnumValue($enum, $strict))->passes($attribute, $value);
        }, __('native-enum::messages.enum_value'));

        $this->app['validator']->extend('enum', function ($attribute, $value, $parameters, $validator) {
            $enum = $parameters[0] ?? null;

            return (new Enum($enum))->passes($attribute, $value);
        }, __('native-enum::messages.enum'));
    }

    private function bootValidationTranslation()
    {
        $this->publishes([
            __DIR__ . '/../lang' => lang_path('vendor/native-enum'),
        ], 'translations');

        $this->loadTranslationsFrom(__DIR__ . '/../lang/', 'native-enum');
    }
}
