<?php

namespace Humweb\Table;

use Humweb\Table\Concerns\HasResourceQueries;
use Humweb\Table\Concerns\Makeable;
use Humweb\Table\Fields\FieldCollection;
use Humweb\Table\Filters\FilterCollection;
use Humweb\Table\Sorts\Sort;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class Resource
{
    use Makeable;
    use HasResourceQueries;

    public string $title;

    public array $parameters = [];

    protected Request $request;

    /**
     * @var FilterCollection
     */
    protected FilterCollection $filters;


    /**
     * @var string|Sort
     */
    public string|Sort $defaultSort = 'id';

    public $driver = 'pgsql';

    public mixed $runtimeTransform = null;

    /**
     * @var string
     */
    protected string $model;

    public function __construct(Request $request, $parameters = [])
    {
        $this->newQuery();
        $this->request    = $request;
        $this->parameters = $parameters;
        $this->filters    = new FilterCollection();
    }

    /**
     * Add parameters from route
     *
     * @param  string|array  $key
     * @param  string|null   $value
     *
     * @return static
     */
    public function addParameter(string|array $key, string $value = null): static
    {
        if (is_array($key)) {
            $this->parameters = array_merge($this->parameters, $key);
        } else {
            $this->parameters[$key] = $value;
        }

        return $this;
    }

    public function toResponse(InertiaTable $table)
    {
        $table->columns($this->getFields())
            ->filters($this->getFilters()->toArray())
            ->records($this->paginate($this->request->get('perPage', 15)))
            ->globalSearch($this->hasGlobalFilter());

        return $table;
    }

    abstract public function fields(): FieldCollection;

    /**
     * @return FieldCollection
     */
    public function getFields(): FieldCollection
    {
        $fields = $this->fields();

        if (is_array($fields)) {
            return new FieldCollection($fields);
        }

        return $fields;
    }

    /**
     * @return FilterCollection
     */
    public function getFilters(): FilterCollection
    {
        $filters = $this->filters();

        if (is_array($filters)) {
            return new FilterCollection($filters);
        }

        return $filters->filter(function ($filter) {
            return !isset($this->parameters[$filter->field]);
        })->values();
    }

    /**
     * @return FilterCollection
     */
    public function filters(): FilterCollection
    {
        return new FilterCollection([]);
    }

    /**
     * @param  string  $name
     * @param  array   $arguments
     *
     * @return mixed
     */
    public function __call(string $name, $arguments)
    {
        return $this->forwardCallTo($this->query, $name, $arguments);
    }

    /**
     * @param  object  $object
     * @param  string  $method
     * @param  array   $parameters
     *
     * @codeCoverageIgnore
     * @return mixed
     */
    protected function forwardCallTo($object, $method, $parameters)
    {
        return $object->{$method}(...$parameters);
    }

    /**
     * @return Builder
     */
    public function getQuery(): Builder
    {
        return $this->query;
    }

    /**
     * @param  callable  $runtimeTransform
     *
     * @return static
     */
    public function runtimeTransform(callable $runtimeTransform): static
    {
        $this->runtimeTransform = $runtimeTransform;

        return $this;
    }
}
