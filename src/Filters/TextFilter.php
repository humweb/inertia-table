<?php

namespace Humweb\Table\Filters;

use Illuminate\Contracts\Database\Query\Builder;
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
        $this->applyWhere($query, $value);

        return $this;
    }

    public function jsonSerialize(): mixed
    {
        return array_merge([
            'component' => $this->component,
            'field' => $this->field,
            'label' => $this->label,
            'value' => $this->value,
            'rules' => $this->rules,
        ], $this->meta());
    }
}
