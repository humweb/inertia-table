<?php

namespace Humweb\Table\Filters;

class EnumFilter extends SelectFilter
{
    public function __construct(string $field, string $label = '', array $options = [], string|array $value = null)
    {
        parent::__construct($field, $label, $options, $value);
        if (! empty($options)) {
            $allowed = implode(',', array_keys($options));
            $this->rules("in:{$allowed}");
        }
    }
}
