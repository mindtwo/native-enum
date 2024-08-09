<?php

namespace mindtwo\NativeEnum\Rules;

use Illuminate\Contracts\Validation\Rule;
use InvalidArgumentException;

class EnumValue implements Rule
{
    /**
     * The name of the rule.
     */
    protected $rule = 'enum_value';

    /**
     * @var string
     */
    protected $enumClass;

    /**
     * @var bool
     */
    protected $strict;

    /**
     * Create a new rule instance.
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    public function __construct(string $enumClass, bool $strict = true)
    {
        $this->enumClass = $enumClass;
        $this->strict = $strict;

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
        return $this->enumClass::hasValue($value, $this->strict);
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message()
    {
        return trans()->has('validation.enum_value')
            ? __('validation.enum_value')
            : __('native-enum::messages.enum_value');
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
        $strict = $this->strict ? 'true' : 'false';

        return "{$this->rule}:{$this->enumClass},{$strict}";
    }
}
