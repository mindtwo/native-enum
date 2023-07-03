<?php

namespace mindtwo\NativeEnum\Tests\Models;

use Illuminate\Database\Eloquent\Casts\Attribute as CastsAttribute;
use Illuminate\Database\Eloquent\Model;
use mindtwo\NativeEnum\Tests\Enums\WithDefaultEnum;

class TestModel extends Model
{
    protected $guarded = ['id'];

    public $casts = [
        'enum' => WithDefaultEnum::class,
    ];

    public function enum(): CastsAttribute
    {
        return CastsAttribute::make(
            get: function ($value) {
                if (null !== ($enum = WithDefaultEnum::tryFrom($value))) {
                    return $enum;
                }

                return WithDefaultEnum::EXAMPLE_1;
            },
        );
    }
}
