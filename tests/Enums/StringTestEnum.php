<?php

namespace mindtwo\NativeEnum\Tests\Enums;

use mindtwo\NativeEnum\BaseEnum;

enum StringTestEnum: string
{
    use BaseEnum;

    case Red = 'red';
    case Blue = 'blue';
    case Empty = '';
    case Padded = ' padded ';
    case WithComma = 'red,blue';
}
