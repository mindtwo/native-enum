<?php

namespace mindtwo\NativeEnum\Rules;

use Illuminate\Contracts\Validation\Rule;
use InvalidArgumentException;

class EnumName implements Rule
{
    /**
     * The name of the rule.
     */
    protected $rule = 'enum_name';

    /**
     * @var string
     */
    protected $enumClass;

    /**
     * Create a new rule instance.
     *
     * @param  string  $enum
     * @return void
     *
     * @throws InvalidArgumentException
     */
    public function __construct(string $enum)
    {
        $this->enumClass = $enum;

        if (! class_exists($this->enumClass)) {
            throw new InvalidArgumentException("Cannot validate against the enum, the class {$this->enumClass} doesn't exist.");
        }
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return $this->enumClass::hasName($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message()
    {
        return trans()->has('validation.enum_name')
            ? __('validation.enum_name')
            : __('native-enum::messages.enum_name');
    }

    /**
     * Convert the rule to a validation string.
     *
     * @return string
     *
     * @see \Illuminate\Validation\ValidationRuleParser::parseParameters
     */
    public function __toString()
    {
        return "{$this->rule}:{$this->enumClass}";
    }
}
