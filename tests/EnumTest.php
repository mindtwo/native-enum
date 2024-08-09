<?php

namespace mindtwo\NativeEnum\Tests;

use Illuminate\Support\Collection;
use mindtwo\NativeEnum\Tests\Enums\TestEnum;
use PHPUnit\Framework\TestCase;

class EnumTest extends TestCase
{
    public function testGetRandomValue()
    {
        $this->assertIsInt(TestEnum::getRandomValue());
    }

    public function testGetRandomName()
    {
        $this->assertIsString(TestEnum::getRandomName());
    }

    public function testGetRandomInstance()
    {
        $this->assertInstanceOf(TestEnum::class, TestEnum::getRandomInstance());
    }

    public function testGetSelectArray()
    {
        $this->assertIsArray(TestEnum::asSelectArray());
    }

    public function testAsArray()
    {
        $this->assertIsArray(TestEnum::asArray());
    }

    public function testAsCollection()
    {
        $this->assertInstanceOf(Collection::class, TestEnum::asCollection());
    }

    public function testGetValues()
    {
        $this->assertIsArray(TestEnum::getValues());

        $values = array_map(function ($case) {
            return $case->value;
        }, TestEnum::cases());

        $this->assertSameSize($values, TestEnum::getValues());
        $this->assertSame($values, TestEnum::getValues());
        $this->assertEquals([TestEnum::EXAMPLE_1->value], TestEnum::getValues('EXAMPLE_1'));
        $this->assertEquals([TestEnum::EXAMPLE_1->value], TestEnum::getValues(TestEnum::EXAMPLE_1));
        $this->assertEquals([TestEnum::EXAMPLE_1->value], TestEnum::getValues([TestEnum::EXAMPLE_1]));
        $this->assertEquals([TestEnum::EXAMPLE_2->value], TestEnum::getValues('EXAMPLE_2'));
    }

    public function testGetNames()
    {
        $this->assertIsArray(TestEnum::getNames());

        $values = array_map(function ($case) {
            return $case->name;
        }, TestEnum::cases());

        $this->assertSameSize($values, TestEnum::getNames());
        $this->assertSame($values, TestEnum::getNames());
        $this->assertEquals([TestEnum::EXAMPLE_1->name], TestEnum::getNames(1));
        $this->assertEquals([TestEnum::EXAMPLE_2->name], TestEnum::getNames(2));
        $this->assertSame($values, TestEnum::getNames([1, 2]));
    }
}
