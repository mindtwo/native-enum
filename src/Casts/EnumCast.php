<?php

namespace mindtwo\NativeEnum\Casts;

use BadMethodCallException;
use Illuminate\Database\Eloquent\Model;
use mindtwo\NativeEnum\BaseEnum;
use mindtwo\NativeEnum\Exceptions\NotNullableEnumField;

class EnumCast extends Cast
{
    /**
     * @param  Model  $model
     * @param  int|string|null|mixed  $value
     * @return BaseEnum|null
     *
     * @throws BadMethodCallException
     * @throws NotNullableEnumField
     */
    public function get($model, string $key, $value, array $attributes)
    {
        if (is_null($value)) {
            return $this->handleNullValue($model, $key);
        }

        return $this->asEnum($value);
    }

    /**
     * @param  Model  $model
     * @param  int|string|BaseEnum|null|mixed  $value
     * @return int|string|null
     *
     * @throws BadMethodCallException
     * @throws NotNullableEnumField
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if (is_null($value)) {
            return $this->handleNullValue($model, $key);
        }

        return $this->asEnum($value)->value;
    }
}
