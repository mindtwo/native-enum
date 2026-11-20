<?php

namespace mindtwo\NativeEnum\Tests\Enums;

use mindtwo\NativeEnum\BaseEnum;

enum OtherTestEnum: int
{
    use BaseEnum;

    case SharedWithTestEnum = 1;
    case UnknownToTestEnum = 99;
}
