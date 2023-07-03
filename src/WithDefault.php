<?php

namespace mindtwo\NativeEnum;

use mindtwo\NativeEnum\Casts\WithDefaultCast;

trait WithDefault
{
    /**
     * Get the name of the caster class to use when casting from / to this cast target.
     *
     * @param  array<string, mixed>  $arguments
     */
    public static function castUsing(array $arguments): string
    {
        return WithDefaultCast::class;
    }

}
