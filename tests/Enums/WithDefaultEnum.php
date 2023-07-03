<?php

namespace mindtwo\NativeEnum\Tests\Enums;

use Illuminate\Contracts\Database\Eloquent\Castable;
use mindtwo\NativeEnum\BaseEnum;
use mindtwo\NativeEnum\WithDefault;

enum WithDefaultEnum: int implements Castable
{
    use BaseEnum;
    use WithDefault;

    case EXAMPLE_1 = 1;
    case EXAMPLE_2 = 2;
}
