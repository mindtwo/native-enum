<?php

namespace mindtwo\NativeEnum;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use mindtwo\NativeEnum\Contracts\LocalizedEnum;

trait BaseEnum
{
    /**
     * Get all or a custom set of the enum names.
     *
     * @param  mixed  $values
     *
     * @return array
     */
    public static function getNames($values = null): array
    {
        if (is_null($values)) {
            return collect(static::cases())->map(function ($case) {
                return $case->name;
            })->toArray();
        }

        return collect(Arr::wrap($values))->map(function ($value) {
            return static::getName($value);
        })->toArray();
    }

    /**
     * Get all or a custom set of the enum values.
     *
     * @param  string|string[]|null  $names
     *
     * @return array
     */
    public static function getValues($names = null): array
    {
        if (is_null($names)) {
            return collect(static::cases())->map(function ($case) {
                return $case->value;
            })->toArray();
        }

        return collect(Arr::wrap($names))->map(function ($name) {
            return static::getValue($name);
        })->toArray();
    }

    /**
     * Get the name for a single enum value.
     *
     * @param  mixed  $value
     * @return string
     */
    public static function getName($value): string
    {
        return collect(static::cases())->sole(function ($case) use ($value) {
            return $value === $case->value;
        })->name;
    }

    /**
     * Get the value for a single enum name.
     *
     * @param  self|string  $name
     * @return mixed
     */
    public static function getValue(self|string $name)
    {
        return collect(static::cases())->sole(function ($case) use ($name) {
            return (optional($name)->name ?? $name) === $case->name;
        })->value;
    }

    /**
     * Check that the enum implements the LocalizedEnum interface.
     *
     * @return bool
     */
    protected static function isLocalizable(): bool
    {
        return isset(class_implements(static::class)[LocalizedEnum::class]);
    }

    /**
     * Get the localized description of a value.
     *
     * This works only if localization is enabled
     * for the enum and if the name exists in the lang file.
     *
     * @param  mixed  $value
     * @return string|null
     */
    protected static function getLocalizedDescription($value): ?string
    {
        if (static::isLocalizable()) {
            $localizedStringName = static::getLocalizationName() . '.' . $value;

            if (Lang::has($localizedStringName)) {
                return __($localizedStringName);
            }
        }

        return $value;
    }

    /**
     * Get the default localization name.
     *
     * @return string
     */
    public static function getLocalizationName(): string
    {
        return 'enums.' . static::class;
    }

    /**
     * Get a random name from the enum.
     *
     * @return string
     */
    public static function getRandomName(): string
    {
        $names = static::getNames();

        return $names[array_rand($names)];
    }

    /**
     * Get a random value from the enum.
     *
     * @return mixed
     */
    public static function getRandomValue()
    {
        $values = static::getValues();

        return $values[array_rand($values)];
    }

    /**
     * Get a random instance of the enum.
     *
     * @return static
     */
    public static function getRandomInstance(): static
    {
        return static::cases()[array_rand(static::cases())];
    }

    /**
     * Return the enum as an array.
     *
     * [string $key => mixed $value]
     *
     * @return Collection
     */
    public static function asCollection(): Collection
    {
        return collect(static::cases())->mapWithKeys(function ($case) {
            return [$case->value => $case->name];
        });
    }

    /**
     * Return the enum as an array.
     *
     * [string $key => mixed $value]
     *
     * @return array
     */
    public static function asArray(): array
    {
        return static::asCollection()->toArray();
    }

    /**
     * Get the enum as an array formatted for a select array.
     *
     * [mixed $value => string description]
     *
     * @return array
     */
    public static function asSelectArray(): array
    {
        return static::asCollection()->map(function ($name) {
            return static::getLocalizedDescription($name);
        })->toArray();
    }
}
