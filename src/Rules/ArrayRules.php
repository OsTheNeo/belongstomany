<?php

namespace Ostheneo\Belongstomany\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class ArrayRules implements Rule
{
    public $rules = [];
    protected $message;

    /**
     * Create a new rule instance.
     */
    public function __construct(array $rules)
    {
        array_push($rules, 'array');
        $this->rules = $rules;
    }

    /**
     * Determine if the validation rule passes.
     */
    public function passes($attribute, $value): bool
    {
        $input = [$attribute => json_decode($value, true)];
        $this->rules = [$attribute => $this->rules];
        $validator = Validator::make($input, $this->rules, $this->messages($attribute));
        $this->message = $validator->errors()->get($attribute);
        return $validator->passes();
    }

    /**
     * Get the validation error message.
     */
    public function message()
    {
        return $this->message;
    }

    public function messages($attribute): array
    {
        return [];
    }
}
