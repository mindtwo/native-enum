<?php

namespace mindtwo\NativeEnum\Tests\Enums;

use mindtwo\NativeEnum\BaseEnum;

enum NumericStringTestEnum: string
{
    use BaseEnum;

    case Beta = '1';
    case Alpha = '0';
}
