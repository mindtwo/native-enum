<?php

namespace mindtwo\NativeEnum\Casts;

use BadMethodCallException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use JsonException;
use mindtwo\NativeEnum\BaseEnum;
use mindtwo\NativeEnum\Exceptions\NotNullableEnumField;
use TypeError;
use UnexpectedValueException;

class EnumCollectionCast extends Cast
{
    public const FORMAT_JSON = 'json';

    public const FORMAT_COMMA = 'comma';

    protected string $format = 'json';

    public function __construct(string $enumClass, ...$options)
    {
        parent::__construct($enumClass, ...$options);

        $this->format = in_array(self::FORMAT_COMMA, $options)
            ? self::FORMAT_COMMA
            : self::FORMAT_JSON;
    }

    /**
     * @param  Model  $model
     * @param  string|null|mixed  $value
     * @return BaseEnum[]|null
     *
     * @throws BadMethodCallException
     * @throws NotNullableEnumField
     */
    public function get($model, string $key, $value, array $attributes)
    {
        if (is_null($value)) {
            return $this->handleNullValue($model, $key);
        }

        return $this->asEnums($this->fromDatabase($value));
    }

    /**
     * @param  Model  $model
     * @param  int[]|string[]|BaseEnum[]|null|mixed  $value
     * @return string|null
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if (is_null($value)) {
            return $this->handleNullValue($model, $key);
        }

        return $this->toDatabase($this->asEnums(Arr::wrap($value)));
    }

    /**
     * The keys are dropped, so that an array with gaps (the result of
     * `array_filter()`, for example) is stored as a JSON list and not as a
     * JSON object.
     *
     * @param  int[]|string[]|BaseEnum[]  $values
     * @return BaseEnum[]
     *
     * @throws TypeError
     * @throws BadMethodCallException
     */
    protected function asEnums(array $values): array
    {
        return array_map([$this, 'asEnum'], array_values($values));
    }

    /**
     * Serialize an array of enum cases for storage.
     *
     * For the `comma` format we project each case to its primitive `value`
     * before joining. PHP enums (both int- and string-backed) do not
     * implement `__toString()` by default, so passing the case objects
     * straight to `implode()` would throw "Object of class X could not be
     * converted to string". Always go through `->value` to support both
     * backing types uniformly.
     *
     * @param  BaseEnum[]  $enums
     */
    protected function toDatabase(array $enums): string
    {
        if ($this->format === self::FORMAT_COMMA) {
            $values = array_map(fn ($enum) => $enum->value, $enums);

            foreach ($values as $value) {
                $isUnsafeString = is_string($value)
                    && ($value === '' || trim($value) !== $value || str_contains($value, ','));

                if ($isUnsafeString) {
                    throw new InvalidArgumentException(
                        'Comma storage cannot represent empty, comma-containing, or padded string values. '.
                        'Use JSON storage instead.'
                    );
                }
            }

            return implode(',', $values);
        }

        return json_encode(array_map(fn ($enum) => $enum->value, $enums));
    }

    /**
     * Deserialize the raw column value into an array of primitive values.
     *
     * For the `comma` format we coerce strings to integers when the enum
     * is int-backed. The database may store the column as VARCHAR/TEXT,
     * which means the values come back as strings even though `->value` on
     * each case is an int. Without coercion, `getInstance()` would fail the
     * strict `===` comparison. For string-backed enums we leave the values
     * as strings.
     *
     * @return array<int, int|string>
     *
     * @throws UnexpectedValueException
     */
    protected function fromDatabase(string $value): array
    {
        if ($this->format !== self::FORMAT_COMMA) {
            return $this->decodeJsonArray($value);
        }

        if ($this->isEmptyCommaValue($value)) {
            return [];
        }

        $values = array_map('trim', explode(',', $value));

        if (! $this->isIntBacked()) {
            return $values;
        }

        return array_map($this->toIntIfValid(...), $values);
    }

    /**
     * Anything but a JSON array is rejected. Reading a damaged column as an
     * empty list would let the next save overwrite what is left of the data.
     * An empty string holds nothing that could be lost and stays readable.
     *
     * @return array<int|string, mixed>
     *
     * @throws UnexpectedValueException
     */
    private function decodeJsonArray(string $value): array
    {
        if ($value === '') {
            return [];
        }

        try {
            $values = json_decode($value, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new UnexpectedValueException(
                "Cannot read {$this->enumClass} cases from a column value that is not valid JSON.",
                previous: $exception
            );
        }

        if (! is_array($values)) {
            throw new UnexpectedValueException(
                "Cannot read {$this->enumClass} cases from a column value that is not a JSON array."
            );
        }

        return $values;
    }

    /**
     * A lone "0" has always been read as an empty list. That is kept, unless
     * the enum has a zero case: then "0" is real data and must not be dropped.
     */
    private function isEmptyCommaValue(string $value): bool
    {
        if ($value === '') {
            return true;
        }

        return $value === '0' && ! $this->enumClass::hasValue(0, false);
    }

    private function isIntBacked(): bool
    {
        $cases = $this->enumClass::cases();

        return $cases !== [] && is_int($cases[0]->value);
    }

    /**
     * Unlike `intval()`, a malformed part such as "1abc" or "abc" is returned
     * unchanged, so the enum lookup rejects it instead of matching a wrong case.
     */
    private function toIntIfValid(string $value): int|string
    {
        $int = filter_var($value, FILTER_VALIDATE_INT);

        return $int === false ? $value : $int;
    }
}
