<?php

namespace Humweb\Table\Filters;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\Request;

class RelationshipFilter extends Filter
{
    public string $component = 'relationship-filter';

    public string $relation;
    public string $column = 'id';

    public static function make(string $relation, string $column = 'id'): static
    {
        $instance = new static($relation, $relation);
        $instance->relation = $relation;
        $instance->column = $column;

        return $instance;
    }

    public function apply(Request $request, Builder $query, $value)
    {
        if ($value === null || $value === '') {
            return $this;
        }

        $query->whereHas($this->relation, function ($q) use ($value) {
            $q->where($this->column, $value);
        });

        return $this;
    }
}
