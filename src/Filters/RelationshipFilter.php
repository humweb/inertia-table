<?php

namespace Humweb\Table\Filters;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\Request;

class RelationshipFilter extends Filter
{
    public string $component = 'relationship-filter';

    public string $relation;
    public string $column = 'id';

    /**
     * Lazy options state
     */
    protected ?string $optionsModelClass = null;
    protected string $optionsLabel = 'name';
    protected string $optionsKey = 'id';
    /** @var callable|null */
    protected $optionsQueryMutator = null;

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
        // Store definition for lazy resolution during serialization only
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

        $this->options = $query->get([$this->optionsKey, $this->optionsLabel])
            ->pluck($this->optionsLabel, $this->optionsKey)
            ->toArray();
    }

    public function jsonSerialize(): mixed
    {
        $this->resolveOptionsIfNeeded();

        return parent::jsonSerialize();
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
