<?php

namespace Humweb\Table\Validation;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

trait HasValidationRules
{
    /**
     *
     * @var mixed
     */
    public mixed $rules = '';

    /**
     * Set the validation rules for the field.
     *
     * @param  callable|array|string  $rules
     *
     * @return HasValidationRules
     */
    public function rules(callable|array|string $rules): self
    {
        $this->rules = ($rules instanceof Rule || is_string($rules)) ? func_get_args() : $rules;

        return $this;
    }

    /**
     * Get the validation rules for this field.
     *
     * @param  Request  $request
     *
     * @return array
     */
    public function getRules(Request $request): array
    {
        return is_callable($this->rules) ? call_user_func($this->rules, $request) : $this->rules;
    }
}
