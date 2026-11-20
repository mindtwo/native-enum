<?php

namespace mindtwo\NativeEnum\Tests\Rules;

use mindtwo\NativeEnum\Rules\Enum;
use mindtwo\NativeEnum\Rules\EnumName;
use mindtwo\NativeEnum\Rules\EnumValue;
use mindtwo\NativeEnum\Tests\Enums\TestEnum;
use PHPUnit\Framework\TestCase;

class EnumRulesTest extends TestCase
{
    // ----- EnumValue -----

    public function testEnumValuePassesForValidValue(): void
    {
        $rule = new EnumValue(TestEnum::class);

        $this->assertTrue($rule->passes('field', 1));
    }

    public function testEnumValueFailsForUnknownValue(): void
    {
        $rule = new EnumValue(TestEnum::class);

        $this->assertFalse($rule->passes('field', 999));
    }

    public function testEnumValueStrictRejectsStringCoercion(): void
    {
        $rule = new EnumValue(TestEnum::class, true);

        $this->assertFalse($rule->passes('field', '1'));
    }

    public function testEnumValueLenientAcceptsStringCoercion(): void
    {
        $rule = new EnumValue(TestEnum::class, false);

        $this->assertTrue($rule->passes('field', '1'));
    }

    // ----- EnumName -----

    public function testEnumNamePassesForValidName(): void
    {
        $rule = new EnumName(TestEnum::class);

        $this->assertTrue($rule->passes('field', 'EXAMPLE_1'));
    }

    public function testEnumNameFailsForUnknownName(): void
    {
        $rule = new EnumName(TestEnum::class);

        $this->assertFalse($rule->passes('field', 'NOPE'));
    }

    // ----- Enum (instance) -----

    public function testEnumRulePassesForEnumCaseInstance(): void
    {
        $rule = new Enum(TestEnum::class);

        $this->assertTrue($rule->passes('field', TestEnum::EXAMPLE_1));
    }

    public function testEnumRuleFailsForRawValue(): void
    {
        $rule = new Enum(TestEnum::class);

        $this->assertFalse($rule->passes('field', 1));
    }

    public function testEnumRuleFailsForUnknownClassInstance(): void
    {
        $rule = new Enum(TestEnum::class);

        $other = new \stdClass;

        $this->assertFalse($rule->passes('field', $other));
    }

    // ----- Constructor validation -----

    public function testEnumValueConstructorRejectsUnknownClass(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new EnumValue('App\\Enums\\DoesNotExist');
    }

    public function testEnumNameConstructorRejectsUnknownClass(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new EnumName('App\\Enums\\DoesNotExist');
    }

    public function testEnumConstructorRejectsUnknownClass(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Enum('App\\Enums\\DoesNotExist');
    }

    // ----- __toString -----

    public function testEnumValueToStringIncludesStrictFlag(): void
    {
        $this->assertSame(
            'enum_value:'.TestEnum::class.',true',
            (string) (new EnumValue(TestEnum::class, true))
        );
        $this->assertSame(
            'enum_value:'.TestEnum::class.',false',
            (string) (new EnumValue(TestEnum::class, false))
        );
    }

    public function testEnumNameToStringRendersRuleName(): void
    {
        $this->assertSame('enum_name:'.TestEnum::class, (string) new EnumName(TestEnum::class));
    }

    public function testEnumToStringRendersRuleName(): void
    {
        $this->assertSame('enum:'.TestEnum::class, (string) new Enum(TestEnum::class));
    }
}
