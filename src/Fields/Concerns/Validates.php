<?php

namespace Humweb\Table\Fields\Concerns;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

trait Validates
{


    /**
     * The validation rules for creation and updates.
     *
     * @var array
     */
    public array $rules = [];


    /**
     * Set the validation rules for the field.
     *
     * @param  callable|array|string  $rules
     *
     * @return ValidatesRequest
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
        return [$this->attribute => is_callable($this->rules) ? call_user_func($this->rules, $request) : $this->rules];
    }

    /**
     * Get the rule attribute
     *
     * @return string
     */
    abstract public function getRuleAttribute(): string;
}
