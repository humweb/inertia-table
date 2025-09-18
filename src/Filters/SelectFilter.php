<?php

namespace Humweb\Table\Filters;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\Request;

class SelectFilter extends Filter
{
    /**
     * @var string
     */
    public string $component = 'select-filter';

    /**
     * @var bool
     */
    public bool $multiple = false;

    /**
     * Lazy options state (for partial reloads)
     */
    protected ?string $optionsModelClass = null;
    protected string $optionsLabel = 'name';
    protected string $optionsKey = 'id';
    /** @var callable|null */
    protected $optionsQueryMutator = null;

    /**
     * @return SelectFilter
     */
    public function multiple(): SelectFilter
    {
        $this->multiple = true;

        return $this;
    }

    /**
     * Populate options lazily from a model (resolved at serialization time only).
     */
    public function fromModel(string $modelClass, string $label = 'name', string $key = 'id', ?callable $queryMutator = null): static
    {
        $this->optionsModelClass = $modelClass;
        $this->optionsLabel = $label;
        $this->optionsKey = $key;
        $this->optionsQueryMutator = $queryMutator;

        return $this;
    }

    protected function resolveOptionsIfNeeded(): void
    {
        if (! empty($this->options)) {
            return;
        }
        if (empty($this->optionsModelClass)) {
            return;
        }

        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = $this->optionsModelClass::query()->orderBy($this->optionsLabel);
        if (is_callable($this->optionsQueryMutator)) {
            ($this->optionsQueryMutator)($query);
        }

        // Return an array of objects with aliased keys for stable ordering and easy sorting on the frontend
        $this->options = $query->get([
            $this->optionsKey.' as key',
            $this->optionsLabel.' as label',
        ])->toArray();
    }

    /**
     * @param  Request       $request
     * @param  Builder       $query
     * @param  string|array  $value
     *
     * @return $this|Filter
     */
    public function apply(Request $request, Builder $query, $value)
    {
        $relation = $this->relation ?: (empty($this->whereHas) ? null : $this->field);
        if (! empty($relation)) {
            $query->whereHas($relation, function ($query) use ($value) {
                // If we don't specify a relation column we assume id or slug
                if (is_bool($this->relationColumn)) {
                    $field = is_numeric($value) ? 'id' : 'slug';
                } else {
                    $field = $this->relationColumn;
                }
                $query->where($field, $value);
            });
        } else {
            if ($this->multiple) {
                $query->whereIn($this->field, $value);
            } else {
                $query->where($this->field, $value);
            }
        }


        return $this;
    }

    public function jsonSerialize(): mixed
    {
        $this->resolveOptionsIfNeeded();

        return array_merge(parent::jsonSerialize(), [
            'multiple' => $this->multiple,
        ]);
    }
}
