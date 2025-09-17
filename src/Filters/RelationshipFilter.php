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
        // Standardize with base Filter relation API
        if (method_exists($instance, 'relation')) {
            $instance->relation($relation, $column);
        }

        return $instance;
    }

    /**
     * Manually set select options (key => label or array of objects on the frontend).
     */
    public function options(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Convenience: populate options from a model query (defaults to id/name).
     */
    public function fromModel(string $modelClass, string $label = 'name', string $key = 'id', ?callable $queryMutator = null): static
    {
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = $modelClass::query()->orderBy($label);
        if ($queryMutator) {
            $queryMutator($query);
        }

        $this->options = $query->get([$key, $label])->pluck($label, $key)->toArray();

        return $this;
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
