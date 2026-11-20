<?php

namespace mindtwo\NativeEnum\Tests\Enums;

use mindtwo\NativeEnum\BaseEnum;

enum ZeroBasedTestEnum: int
{
    use BaseEnum;

    case None = 0;
    case Basic = 1;
}
