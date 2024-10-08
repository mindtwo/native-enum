<?php

declare(strict_types=1);

namespace mindtwo\NativeEnum\Exceptions;

use InvalidArgumentException;

final class NotNullableEnumField extends InvalidArgumentException
{
    public static function make(string $field, string $model): self
    {
        return new self("Field {$field} on model {$model} is not nullable");
    }
}
