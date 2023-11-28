<?php

namespace Humweb\Table;

use Humweb\Table\Concerns\ForwardsCalls;
use Humweb\Table\Concerns\HasResourceQueries;
use Humweb\Table\Concerns\Makeable;
use Humweb\Table\Contracts\FieldCollectionable;
use Humweb\Table\Contracts\FilterCollectionable;
use Humweb\Table\Fields\FieldCollection;
use Humweb\Table\Filters\FilterCollection;
use Humweb\Table\Sorts\Sort;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class Resource
{
    use Makeable;
    use ForwardsCalls;
    use HasResourceQueries;

    public string $title;
    public string $primaryKey = 'id';

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
    protected $model;

    public function __construct(Request $request, $parameters = [])
    {
        $this->newQuery();

        $this->request = $request;
        $this->parameters = $parameters;
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
            ->filters($this->getFilters())
            ->records($this->paginate($this->request->get('perPage', 15)))
            ->globalSearch($this->hasGlobalFilter());

        return $table;
    }

    abstract public function fields(): FieldCollectionable;

    /**
     * @return FieldCollection
     */
    public function getFields(): FieldCollectionable
    {
        return $this->fields();
    }

    public function toForm($model = [])
    {
        $fields = $this->fields();

        $fields->fill($model);

        return $fields;
    }

    /**
     * @return FilterCollection
     */
    public function getFilters(): FilterCollectionable
    {
        // If we pass a matching url parameter to the resource
        // We don't show the filter.
        return $this->filters()->filter(function ($filter) {
            return ! isset($this->parameters[$filter->field]);
        })->values();
    }

    public function filters(): FilterCollectionable
    {
        return new FilterCollection();
    }

    /**
     * @param  string  $name
     * @param  array   $arguments
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        return $this->forwardCallTo($this->query, $name, $arguments);
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
