<?php

namespace mindtwo\NativeEnum\Tests;

use Illuminate\Support\Collection;
use mindtwo\NativeEnum\Tests\Enums\LocalizedTestEnum;
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

    public function testHasValueAndHasName()
    {
        $this->assertTrue(TestEnum::hasValue(1));
        $this->assertFalse(TestEnum::hasValue(999));
        $this->assertFalse(TestEnum::hasValue('1')); // strict by default
        $this->assertTrue(TestEnum::hasValue('1', false)); // non-strict coerces

        $this->assertTrue(TestEnum::hasName('EXAMPLE_1'));
        $this->assertFalse(TestEnum::hasName('NOT_A_CASE'));
    }

    public function testGetNameReturnsNameForValue()
    {
        $this->assertSame('EXAMPLE_1', TestEnum::getName(1));
        $this->assertSame('EXAMPLE_2', TestEnum::getName(2));
    }

    public function testGetInstanceReturnsCaseForValue()
    {
        $this->assertSame(TestEnum::EXAMPLE_1, TestEnum::getInstance(1));
        $this->assertSame(TestEnum::EXAMPLE_2, TestEnum::getInstance(2));
    }

    public function testInvokeReturnsPrimitiveValue()
    {
        $this->assertSame(1, (TestEnum::EXAMPLE_1)());
        $this->assertSame(2, (TestEnum::EXAMPLE_2)());
    }

    public function testCallStaticReturnsPrimitiveValueForCaseName()
    {
        $this->assertSame(1, TestEnum::EXAMPLE_1());
        $this->assertSame(2, TestEnum::EXAMPLE_2());
    }

    public function testCallStaticThrowsForUnknownCase()
    {
        $this->expectException(\mindtwo\NativeEnum\Exceptions\UndefinedCaseError::class);

        TestEnum::DOES_NOT_EXIST();
    }

    public function testToLivewireReturnsValueAndName()
    {
        $wire = TestEnum::EXAMPLE_1->toLivewire();

        $this->assertSame(['value' => 1, 'label' => 'EXAMPLE_1'], $wire);
    }

    public function testFromLivewireReturnsCaseForEnumInstance()
    {
        $this->assertSame(TestEnum::EXAMPLE_1, TestEnum::fromLivewire(TestEnum::EXAMPLE_1));
    }

    public function testFromLivewireReturnsCaseForArrayLikeObject()
    {
        $obj = (object) ['value' => 2];

        $this->assertSame(TestEnum::EXAMPLE_2, TestEnum::fromLivewire($obj));
    }

    public function testAsServableEnumExposesNameValueAndLocalizedName()
    {
        $served = TestEnum::asServableEnum();

        $this->assertArrayHasKey(1, $served);
        $this->assertSame([
            'name' => 'EXAMPLE_1',
            'value' => 1,
            'localized_name' => 'EXAMPLE_1', // no translation file loaded in tests
        ], $served[1]);
    }

    public function testAsSelectArrayIsSorted()
    {
        $select = TestEnum::asSelectArray();

        // sort() in BaseEnum sorts by value (the array keys), ascending
        $this->assertSame([1 => 'EXAMPLE_1', 2 => 'EXAMPLE_2'], $select);
    }

    public function testGetLocalizationNameBuildsDottedKey()
    {
        $this->assertSame('enums.'.TestEnum::class, TestEnum::getLocalizationName());
    }

    /**
     * The localized enum must report itself as localizable. Without an
     * active translator (e.g. in unit tests), `getLocalizedName()` should
     * fall back to the raw value rather than throwing a facade-root
     * exception.
     */
    public function testGetLocalizedNameFallsBackWhenTranslatorIsMissing()
    {
        $this->assertSame('EXAMPLE_1', LocalizedTestEnum::getLocalizedName('EXAMPLE_1'));
        $this->assertSame('EXAMPLE_1', LocalizedTestEnum::EXAMPLE_1->name());
    }

    public function testIsLocalizableReflectsInterface()
    {
        $this->assertTrue(LocalizedTestEnum::isLocalizable());
    }
}
