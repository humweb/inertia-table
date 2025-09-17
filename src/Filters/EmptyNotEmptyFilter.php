<?php

namespace Humweb\Table\Filters;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\Request;

class EmptyNotEmptyFilter extends SelectFilter
{
    public string $component = 'empty-filter';

    public array $options = [
        'empty' => 'Empty',
        'not_empty' => 'Not Empty',
    ];

    public function __construct(string $field, string $label = '', array $options = [], string|array $value = null)
    {
        parent::__construct($field, $label, $options, $value);
        $this->rules('in:empty,not_empty');
    }

    public function apply(Request $request, Builder $query, $value)
    {
        if ($value === 'empty') {
            $query->whereNull($this->field)->orWhere($this->field, '=','');
        } elseif ($value === 'not_empty') {
            $query->whereNotNull($this->field)->where($this->field, '!=','');
        }

        return $this;
    }
}


