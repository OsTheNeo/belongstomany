<?php

namespace Ostheneo\Belongstomany\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

class ArrayRules implements ValidationRule
{
    /**
     * The validation rules to apply.
     */
    protected array $rules = [];

    /**
     * The validation error messages.
     */
    protected array $messages = [];

    /**
     * Create a new rule instance.
     */
    public function __construct(array $rules)
    {
        $this->rules = array_merge(['array'], $rules);
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $decodedValue = is_string($value) ? json_decode($value, true) : $value;
        
        if (json_last_error() !== JSON_ERROR_NONE && is_string($value)) {
            $fail('The :attribute must be a valid JSON array.');
            return;
        }

        $input = [$attribute => $decodedValue];
        $rules = [$attribute => $this->rules];
        
        $validator = Validator::make($input, $rules);

        if ($validator->fails()) {
            foreach ($validator->errors()->get($attribute) as $message) {
                $fail($message);
            }
        }
    }
}
