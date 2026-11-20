<?php

namespace mindtwo\NativeEnum\Tests\Casts;

use Illuminate\Support\ItemNotFoundException;
use mindtwo\NativeEnum\Casts\EnumCast;
use mindtwo\NativeEnum\Exceptions\NotNullableEnumField;
use mindtwo\NativeEnum\Tests\Enums\OtherTestEnum;
use mindtwo\NativeEnum\Tests\Enums\TestEnum;
use PHPUnit\Framework\TestCase;

class EnumCastTest extends TestCase
{
    public function testGetReturnsEnumCaseForIntValue(): void
    {
        $cast = new EnumCast(TestEnum::class);

        $result = $cast->get($this->makeModel(), 'role', 1, []);

        $this->assertSame(TestEnum::EXAMPLE_1, $result);
    }

    public function testGetReturnsNullForNullValueWhenNullable(): void
    {
        $cast = new EnumCast(TestEnum::class, 'nullable');

        $this->assertNull($cast->get($this->makeModel(), 'role', null, []));
    }

    public function testGetThrowsForNullValueWhenNotNullable(): void
    {
        $cast = new EnumCast(TestEnum::class);

        $this->expectException(NotNullableEnumField::class);

        $cast->get($this->makeModel(), 'role', null, []);
    }

    public function testSetReturnsIntForEnumCase(): void
    {
        $cast = new EnumCast(TestEnum::class);

        $result = $cast->set($this->makeModel(), 'role', TestEnum::EXAMPLE_2, []);

        $this->assertSame(2, $result);
    }

    public function testSetReturnsIntForRawIntValue(): void
    {
        $cast = new EnumCast(TestEnum::class);

        $result = $cast->set($this->makeModel(), 'role', 1, []);

        $this->assertSame(1, $result);
    }

    public function testSetReturnsNullForNullWhenNullable(): void
    {
        $cast = new EnumCast(TestEnum::class, 'nullable');

        $this->assertNull($cast->set($this->makeModel(), 'role', null, []));
    }

    public function testSetThrowsForNullWhenNotNullable(): void
    {
        $cast = new EnumCast(TestEnum::class);

        $this->expectException(NotNullableEnumField::class);

        $cast->set($this->makeModel(), 'role', null, []);
    }

    public function testGetThrowsForUnknownValue(): void
    {
        $cast = new EnumCast(TestEnum::class);

        // Collection::sole() throws ItemNotFoundException when no case matches.
        $this->expectException(\Illuminate\Support\ItemNotFoundException::class);

        $cast->get($this->makeModel(), 'role', 999, []);
    }

    public function testSetAcceptsCaseOfAnotherEnumWhenItsValueIsValid(): void
    {
        $cast = new EnumCast(TestEnum::class);

        $result = $cast->set($this->makeModel(), 'role', OtherTestEnum::SharedWithTestEnum, []);

        $this->assertSame(1, $result);
    }

    public function testSetThrowsForCaseOfAnotherEnumWhenItsValueIsUnknown(): void
    {
        $cast = new EnumCast(TestEnum::class);

        $this->expectException(ItemNotFoundException::class);

        $cast->set($this->makeModel(), 'role', OtherTestEnum::UnknownToTestEnum, []);
    }

    /**
     * The cast only needs `$model` for `get_class()` when constructing the
     * exception; it never calls Eloquent methods on it. A stdClass is enough.
     */
    private function makeModel(): object
    {
        return new class {
        };
    }
}
