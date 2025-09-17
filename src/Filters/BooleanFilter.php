<?php

namespace Humweb\Table\Filters;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\Request;

class BooleanFilter extends SelectFilter
{
    public string $component = 'boolean-filter';

    public array $options = [
        '1' => 'True',
        '0' => 'False',
    ];

    public function __construct(string $field, string $label = '', array $options = [], string|array $value = null)
    {
        parent::__construct($field, $label, $options, $value);
        $this->rules('in:1,0,true,false');
        $this->exact();
    }

    public function apply(Request $request, Builder $query, $value)
    {
        // Normalize truthy/falsy to 1/0
        if ($value === true || $value === 'true') {
            $value = 1;
        } elseif ($value === false || $value === 'false') {
            $value = 0;
        }

        return parent::apply($request, $query, $value);
    }
}


