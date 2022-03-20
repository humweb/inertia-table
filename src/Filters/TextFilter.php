<?php

namespace Humweb\Table\Filters;

use Illuminate\Http\Request;

class TextFilter extends Filter
{
    /**
     * @var string
     */
    public string $component = 'text-filter';

    /**
     * @param  Request  $request
     * @param           $query
     * @param           $value
     *
     * @return \Illuminate\Database\Eloquent\Builder|void
     */
    public function apply(Request $request, $query, $value)
    {
        $this->value = $value;
        $this->whereFilter($query, $value);
    }

    public function jsonSerialize()
    {
        return array_merge([
            'component' => $this->component,
            'field'     => $this->field,
            'label'     => $this->label,
            'value'     => $this->value,
            'rules'     => $this->validationRules
        ], $this->meta());
    }
}
