<?php

namespace mindtwo\NativeEnum\Tests\Casts;

use Illuminate\Support\ItemNotFoundException;
use mindtwo\NativeEnum\Casts\EnumCollectionCast;
use mindtwo\NativeEnum\Exceptions\NotNullableEnumField;
use mindtwo\NativeEnum\Tests\Enums\StringTestEnum;
use mindtwo\NativeEnum\Tests\Enums\TestEnum;
use mindtwo\NativeEnum\Tests\Enums\ZeroBasedTestEnum;
use PHPUnit\Framework\TestCase;

class EnumCollectionCastTest extends TestCase
{
    public function testJsonFormatRoundtripForIntBackedEnum(): void
    {
        $cast = new EnumCollectionCast(TestEnum::class);

        $setValue = $cast->set($this->model(), 'roles', [1, 2], []);
        $this->assertSame('[1,2]', $setValue);

        $getValue = $cast->get($this->model(), 'roles', $setValue, []);
        $this->assertEquals([TestEnum::EXAMPLE_1, TestEnum::EXAMPLE_2], $getValue);
    }

    public function testJsonFormatRoundtripAcceptsEnumInstances(): void
    {
        $cast = new EnumCollectionCast(TestEnum::class);

        $setValue = $cast->set($this->model(), 'roles', [TestEnum::EXAMPLE_1], []);
        $this->assertSame('[1]', $setValue);

        $getValue = $cast->get($this->model(), 'roles', $setValue, []);
        $this->assertEquals([TestEnum::EXAMPLE_1], $getValue);
    }

    /**
     * Regression: the comma format previously called `implode(',', $enums)`
     * with enum cases directly, which throws "Object of class X could not
     * be converted to string" because PHP enums don't implement
     * `__toString()` by default. The fix projects each case to `->value`
     * before joining.
     */
    public function testCommaFormatRoundtripForIntBackedEnum(): void
    {
        $cast = new EnumCollectionCast(TestEnum::class, 'comma');

        $setValue = $cast->set($this->model(), 'roles', [TestEnum::EXAMPLE_1, TestEnum::EXAMPLE_2], []);
        $this->assertSame('1,2', $setValue);

        $getValue = $cast->get($this->model(), 'roles', $setValue, []);
        $this->assertEquals([TestEnum::EXAMPLE_1, TestEnum::EXAMPLE_2], $getValue);
    }

    /**
     * Regression: when the database column is VARCHAR/TEXT the values come
     * back as strings even though the enum is int-backed. The deserializer
     * must coerce them back to ints so `getInstance()`'s strict `===`
     * comparison succeeds.
     */
    public function testCommaFormatCoercesStringValuesToIntForIntBackedEnum(): void
    {
        $cast = new EnumCollectionCast(TestEnum::class, 'comma');

        // simulate the column coming back as raw string
        $getValue = $cast->get($this->model(), 'roles', '1,2', []);
        $this->assertEquals([TestEnum::EXAMPLE_1, TestEnum::EXAMPLE_2], $getValue);
    }

    public function testCommaFormatHandlesEmptyString(): void
    {
        $cast = new EnumCollectionCast(TestEnum::class, 'comma');

        $this->assertSame([], $cast->get($this->model(), 'roles', '', []));
    }

    public function testCommaFormatTrimsWhitespace(): void
    {
        $cast = new EnumCollectionCast(TestEnum::class, 'comma');

        $this->assertEquals(
            [TestEnum::EXAMPLE_1, TestEnum::EXAMPLE_2],
            $cast->get($this->model(), 'roles', ' 1 , 2 ', [])
        );
    }

    public function testCommaFormatReadsLoneZeroCase(): void
    {
        $cast = new EnumCollectionCast(ZeroBasedTestEnum::class, 'comma');

        $setValue = $cast->set($this->model(), 'roles', [ZeroBasedTestEnum::None], []);
        $this->assertSame('0', $setValue);

        $getValue = $cast->get($this->model(), 'roles', $setValue, []);
        $this->assertSame([ZeroBasedTestEnum::None], $getValue);
    }

    public function testCommaFormatReadsLoneZeroAsEmptyWhenEnumHasNoZeroCase(): void
    {
        $cast = new EnumCollectionCast(TestEnum::class, 'comma');

        $this->assertSame([], $cast->get($this->model(), 'roles', '0', []));
    }

    public function testCommaFormatThrowsForPartWithTrailingCharacters(): void
    {
        $cast = new EnumCollectionCast(TestEnum::class, 'comma');

        $this->expectException(ItemNotFoundException::class);

        $cast->get($this->model(), 'roles', '1abc', []);
    }

    public function testCommaFormatThrowsForNonNumericPartEvenWhenEnumHasZeroCase(): void
    {
        $cast = new EnumCollectionCast(ZeroBasedTestEnum::class, 'comma');

        $this->expectException(ItemNotFoundException::class);

        $cast->get($this->model(), 'roles', 'abc', []);
    }

    public function testCommaFormatRoundtripForStringBackedEnum(): void
    {
        $cast = new EnumCollectionCast(StringTestEnum::class, 'comma');

        $setValue = $cast->set($this->model(), 'colors', [StringTestEnum::Red, StringTestEnum::Blue], []);
        $this->assertSame('red,blue', $setValue);

        $getValue = $cast->get($this->model(), 'colors', $setValue, []);
        $this->assertSame([StringTestEnum::Red, StringTestEnum::Blue], $getValue);
    }

    public function testGetReturnsNullWhenNullableAndValueIsNull(): void
    {
        $cast = new EnumCollectionCast(TestEnum::class, 'nullable');

        $this->assertNull($cast->get($this->model(), 'roles', null, []));
        $this->assertNull($cast->set($this->model(), 'roles', null, []));
    }

    public function testGetThrowsForNullWhenNotNullable(): void
    {
        $cast = new EnumCollectionCast(TestEnum::class);

        $this->expectException(NotNullableEnumField::class);

        $cast->get($this->model(), 'roles', null, []);
    }

    public function testSetThrowsForNullWhenNotNullable(): void
    {
        $cast = new EnumCollectionCast(TestEnum::class);

        $this->expectException(NotNullableEnumField::class);

        $cast->set($this->model(), 'roles', null, []);
    }

    public function testDefaultFormatIsJson(): void
    {
        $cast = new EnumCollectionCast(TestEnum::class);

        $this->assertSame('[1]', $cast->set($this->model(), 'roles', [1], []));
    }

    /**
     * The cast only needs `$model` for `get_class()` when constructing the
     * exception; it never calls Eloquent methods on it. A stdClass is enough.
     */
    private function model(): object
    {
        return new class {};
    }
}
