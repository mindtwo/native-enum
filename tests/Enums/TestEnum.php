<?php

namespace mindtwo\NativeEnum\Tests\Enums;

use mindtwo\NativeEnum\BaseEnum;

enum TestEnum: int
{
    use BaseEnum;

    case EXAMPLE_1 = 1;
    case EXAMPLE_2 = 2;
}
