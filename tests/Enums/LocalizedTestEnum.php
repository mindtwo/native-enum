<?php

namespace mindtwo\NativeEnum\Tests\Enums;

use mindtwo\NativeEnum\BaseEnum;
use mindtwo\NativeEnum\Contracts\LocalizedEnum;

enum LocalizedTestEnum: int implements LocalizedEnum
{
    use BaseEnum;

    case EXAMPLE_1 = 1;
    case EXAMPLE_2 = 2;
}
