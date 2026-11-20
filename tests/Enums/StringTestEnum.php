<?php

namespace mindtwo\NativeEnum\Tests\Enums;

use mindtwo\NativeEnum\BaseEnum;

enum StringTestEnum: string
{
    use BaseEnum;

    case Red = 'red';
    case Blue = 'blue';
}
