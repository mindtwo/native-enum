<?php

namespace mindtwo\NativeEnum;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use mindtwo\NativeEnum\Casts\EnumCast;
use mindtwo\NativeEnum\Casts\EnumCollectionCast;
use mindtwo\NativeEnum\Contracts\LocalizedEnum;

trait BaseEnum
{
    /**
     * Get all or a custom set of the enum names.
     *
     * @param  mixed  $values
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
     */
    public static function getName($value): string
    {
        return collect(static::cases())->sole(function ($case) use ($value) {
            return $value === $case->value;
        })->name;
    }

    /**
     * Get the instance for a single enum value.
     *
     * @param  mixed  $value
     */
    public static function getInstance($value): self
    {
        return collect(static::cases())->sole(function ($case) use ($value) {
            return $value === $case->value;
        });
    }

    /**
     * Get the value for a single enum name.
     *
     * @return mixed
     */
    public static function getValue(self|string $name)
    {
        return collect(static::cases())->sole(function ($case) use ($name) {
            return (optional($name)->name ?? $name) === $case->name;
        })->value;
    }

    /**
     * Check that the enum contains a specific key.
     */
    public static function hasName(string $key): bool
    {
        return in_array($key, static::getNames(), true);
    }

    /**
     * Check that the enum contains a specific value.
     *
     * @param  mixed  $value
     * @param  bool  $strict  (Optional, defaults to True)
     */
    public static function hasValue($value, bool $strict = true): bool
    {
        $validValues = static::getValues();

        if ($strict) {
            return in_array($value, $validValues, true);
        }

        return in_array((string) $value, array_map('strval', $validValues), true);
    }

    /**
     * Check that the enum implements the LocalizedEnum interface.
     */
    protected static function isLocalizable(): bool
    {
        return isset(class_implements(static::class)[LocalizedEnum::class]);
    }

    /**
     * Get the localized name of a value.
     *
     * This works only if localization is enabled
     * for the enum and if the name exists in the lang file.
     *
     * @param  mixed  $value
     */
    public static function getLocalizedName($value): ?string
    {
        if (static::isLocalizable()) {
            $localizedStringName = static::getLocalizationName().'.'.$value;

            if (Lang::has($localizedStringName)) {
                return __($localizedStringName);
            }
        }

        return $value;
    }

    /**
     * Get the localized name of the enum.
     */
    public function name(): ?string
    {
        return $this->getLocalizedName($this->name);
    }

    /**
     * Get the default localization name.
     */
    public static function getLocalizationName(): string
    {
        return 'enums.'.static::class;
    }

    /**
     * Get a random name from the enum.
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
     */
    public static function getRandomInstance(): static
    {
        return static::cases()[array_rand(static::cases())];
    }

    /**
     * Return the enum as an array.
     *
     * [string $key => mixed $value]
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
     */
    public static function asArray(): array
    {
        return static::asCollection()->toArray();
    }

    /**
     * Get the enum as an array formatted for a select array.
     *
     * [mixed $value => string description]
     */
    public static function asSelectArray(): array
    {
        return static::asCollection()->map(function ($name) {
            return static::getLocalizedName($name);
        })->sort()->toArray();
    }

    /**
     * Get the enum as an array formatted for a js servable array.
     *
     * [mixed $value => string description]
     */
    public static function asServableEnum(): array
    {
        return static::asCollection()->map(function ($name, $value) {
            return [
                'name' => $name,
                'value' => $value,
                'localized_name' => static::getLocalizedName($name),
            ];
        })->sort()->toArray();
    }

    /**
     * Allow casting the enum to a collection and nullable enums.
     */
    public static function castUsing(array $arguments): CastsAttributes
    {
        if (in_array('collection', $arguments)) {
            return new EnumCollectionCast(static::class, ...$arguments);
        }

        return new EnumCast(static::class, ...$arguments);
    }

    /**
     * Livewire friendly method to create an enum instance.
     */
    public static function fromLivewire($enum): self
    {
        if (is_a($enum, self::class)) {
            return $enum;
        }

        return self::from($enum->value);
    }

    /**
     * Livewire friendly method to create an enum instance.
     */
    public function toLivewire(): array
    {
        return [
            'value' => $this->value,
            'label' => $this->name,
        ];
    }

    /** Return the enum's value when it's $invoked(). */
    public function __invoke()
    {
        return $this->value;
    }

    /** Return the enum's value when it's called ::STATICALLY(). */
    public static function __callStatic($name, $args)
    {
        $cases = static::cases();

        foreach ($cases as $case) {
            if ($case->name === $name) {
                return $case->value;
            }
        }

        throw new Exceptions\UndefinedCaseError(static::class, $name);
    }
}
