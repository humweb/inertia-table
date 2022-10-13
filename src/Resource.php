<?php

namespace Humweb\Table;

use Humweb\Table\Fields\FieldCollection;
use Humweb\Table\Filters\FilterCollection;
use Humweb\Table\Sorts\Sort;
use Humweb\Table\Traits\Makeable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

abstract class Resource
{
    use Makeable;

    public array $parameters = [];

    protected Request $request;
    /**
     * @var FilterCollection
     */
    protected FilterCollection $filters;
    /**
     * @var Builder
     */
    protected Builder $query;

    /**
     * @var array
     */
    protected array $sorts;

    /**
     * @var string|Sort
     */
    public string|Sort $defaultSort = 'id';

    public $driver = 'pgsql';

    /**
     * @var string
     */
    protected $model;

    public function __construct(Request $request, $parameters = [])
    {
        $this->newQuery();
        $this->request    = $request;
        $this->parameters = $parameters;
        $this->filters    = new FilterCollection();
    }

    /**
     * Query and paginate data from database
     *
     * @param  int     $perPage
     * @param  array   $columns
     * @param  string  $pageName
     * @param  int     $page
     *
     * @return mixed
     */
    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $this->buildQuery();

        return $this->query->paginate($perPage, $columns, $pageName, $page)->withQueryString();
    }

    public function buildQuery()
    {
        $this->applyDefaultSort()
            ->applySorts()
            ->applyGlobalFilter()
            ->applyCustomFilters()
            ->applySearch()
            ->applyFilters();
    }

    public function applySearch()
    {
        $reqSearch = $this->request->get('search');

        if ($reqSearch) {
            $this->getFields()->filter(fn($f) => $f->searchable)->each(function ($field) use ($reqSearch) {
                if (isset($reqSearch[$field->attribute]) && !empty($reqSearch[$field->attribute])) {
                    $this->whereLike($field->attribute, $reqSearch[$field->attribute]);
                }
            });
        }

        return $this;
    }

    public function whereLike($field, $value)
    {
        if ($this->driver == 'pgsql') {
            $like = 'ilike';
        } elseif ($this->driver == 'sqlite') {
            $like = 'like';
        } else {
            $field = "LOWER('{$field}')";
            $like  = 'like';
        }

        $this->query->where(DB::raw($field), $like, '%'.strtolower($value).'%');
    }

    /**
     * Add parameters from route
     *
     * @param  string|array  $key
     * @param  string|null   $value
     *
     * @return $this
     */
    public function addParameter(string|array $key, string $value = null): Resource
    {
        if (is_array($key)) {
            $this->parameters = array_merge($this->parameters, $key);
        } else {
            $this->parameters[$key] = $value;
        }

        return $this;
    }

    /**
     * Create new query builder instance
     *
     * @return $this
     */
    public function newQuery(): Resource
    {
        $this->query  = $this->model::query();
        $this->driver = $this->query->getConnection()->getDriverName();

        return $this;
    }

    /**
     * Apply custom filters to query
     *
     * @return $this
     */
    public function applyCustomFilters(): Resource
    {
        foreach ($this->parameters as $key => $value) {
            $method = 'filter'.Str::studly(str_replace('.', '_', $key));
            if (method_exists($this, $method)) {
                $this->{$method}($value);
            }
        }

        return $this;
    }

    /**
     * Add allowed filters to query builder
     *
     * @return $this
     */
    protected function applyFilters(): Resource
    {
        $this->getFilters()->apply($this->request, $this->query);


        return $this;
    }

    /**
     * Apply default sort to builder
     *
     * @return $this
     */
    public function applyDefaultSort(): Resource
    {
        if ($this->request->has('sort')) {
            return $this;
        }

        if (method_exists($this, 'defaultSort')) {
            $this->defaultSort($this->query);
        } elseif (is_string($this->defaultSort)) {
            $this->request->merge(['sort' => $this->defaultSort]);
        }

        return $this;
    }

    /**
     * Apply default sort to builder
     *
     * @return $this
     */
    public function applySorts(): Resource
    {
        if ($this->request->has('sort')) {
            $sortField  = $this->request->get('sort');
            $descending = str_starts_with($sortField, '-');

            if ($descending) {
                $sortField = str_replace('-', '', $sortField);
            }

            $this->getFields()->each(function ($field) use ($sortField, $descending) {
                if ($field->attribute == $sortField && $field->sortable) {
                    ($field->sortableStrategy)($this->query, $descending, $sortField);
                }
            });
        }

        return $this;
    }

    /**
     * Add global filter if it exists
     *
     * @return $this
     */
    public function applyGlobalFilter(): Resource
    {
        if (method_exists($this, 'globalFilter') && $this->requestHasGlobalFilter()) {
            $this->globalFilter($this->query, $this->request->get('search')['global']);
        }

        return $this;
    }

    public function requestHasGlobalFilter()
    {
        return array_key_exists('global', $this->request->get('search', []));
    }

    /**
     * @codeCoverageIgnore
     *
     * @return bool
     */
    public function hasGlobalFilter()
    {
        return method_exists($this, 'globalFilter');
    }

    public function toResponse(InertiaTable $table)
    {
        $table->columns($this->getFields())
            ->filters($this->getFilters()->toArray())
            ->records($this->paginate())
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
    public function filters()
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
}
