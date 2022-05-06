<?php

namespace Humweb\Table\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TextFilter extends Filter
{
    /**
     * @var string
     */
    public string $component = 'text-filter';

    /**
     * @param  Request       $request
     * @param  Builder       $query
     * @param  string|array  $value
     *
     * @return $this|Filter
     */
    public function apply(Request $request, Builder $query, $value)
    {
        $this->value = $value;
        $this->whereFilter($query, $value);

        return $this;
    }

    public function jsonSerialize()
    {
        return array_merge([
            'component' => $this->component,
            'field' => $this->field,
            'label' => $this->label,
            'value' => $this->value,
            'rules' => $this->validationRules,
        ], $this->meta());
    }
}
