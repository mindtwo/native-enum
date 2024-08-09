<?php

namespace mindtwo\NativeEnum\Casts;

use BadMethodCallException;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
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
    protected function asEnum($value): Enum
    {
        if ($value instanceof Enum) {
            return $value;
        }

        return forward_static_call(
            [$this->enumClass, 'make'],
            $value
        );
    }

    /**
     * @return null
     *
     * @throws NotNullableEnumField
     */
    protected function handleNullValue(Model $model, string $key)
    {
        if ($this->isNullable) {
            return null;
        }

        throw NotNullableEnumField::make($key, get_class($model));
    }
}
