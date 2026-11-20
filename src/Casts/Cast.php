<?php

namespace mindtwo\NativeEnum\Casts;

use BadMethodCallException;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use mindtwo\NativeEnum\BaseEnum;
use mindtwo\NativeEnum\Exceptions\NotNullableEnumField;
use TypeError;

abstract class Cast implements CastsAttributes
{
    /**
     * @psalm-var class-string<BaseEnum>
     */
    protected string $enumClass;

    protected bool $isNullable = false;

    /**
     * Cast constructor.
     *
     * @psalm-param class-string<BaseEnum> $enumClass
     *
     * @param  string[]  ...$options
     */
    public function __construct(string $enumClass, ...$options)
    {
        $this->enumClass = $enumClass;

        $this->isNullable = in_array('nullable', $options);
    }

    /**
     * @param  int|string|BaseEnum  $value
     * @return BaseEnum
     *
     * @throws TypeError
     * @throws BadMethodCallException
     *
     * @see BaseEnum::make()
     */
    protected function asEnum($value)
    {
        if ($value instanceof $this->enumClass) {
            return $value;
        }

        // A case of another enum is only accepted if its value is valid for this enum.
        if (is_object($value) && in_array(BaseEnum::class, class_uses($value), true)) {
            $value = $value->value;
        }

        return $this->enumClass::getInstance($value);
    }

    /**
     * @param  object  $model  Any object — typically an Eloquent Model, but
     *                         accepted untyped so the cast can be unit-tested
     *                         without `illuminate/database` installed.
     *
     * @throws NotNullableEnumField
     */
    protected function handleNullValue(object $model, string $key): null
    {
        if ($this->isNullable) {
            return null;
        }

        throw NotNullableEnumField::make($key, get_class($model));
    }
}
