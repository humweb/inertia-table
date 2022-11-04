<?php

namespace Humweb\Table\Validation;

use Illuminate\Http\Request;

trait HasValidationRules
{
    /**
     *
     * @var array
     */
    public array $rules = [];

    /**
     * @param  callable|array|string  $rules
     *
     * @return $this
     */
    public function rules(callable|array|string $rules): self
    {
        $this->rules = (is_array($rules)) ? $rules : func_get_args();

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
        return is_callable($this->rules[0]) ? call_user_func($this->rules[0], $request) : $this->rules;
    }
}
