<?php

namespace Humweb\InertiaTable;

use Humweb\InertiaTable\Export\QueryExport;
use Humweb\InertiaTable\Support\Makeable;
use Spatie\QueryBuilder\QueryBuilder;

abstract class TableResource
{
    use Makeable;

    protected array $parameters = [];

    protected array $filters;
    protected array $sorts;
    protected $defaultSort = 'id';

    protected $model;

    public function __construct($parameters = [])
    {
        $this->newQuery();

        $this->parameters = $parameters;
    }

    /**
     * Add filter for allowed filters
     *
     * @param $filter
     *
     * @return $this
     */
    public function addFilter($filter): TableResource
    {
        if (is_array($filter)) {
            $this->filters = array_merge($this->filters, $filter);
        } else {
            $this->filters[] = $filter;
        }

        return $this;
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
            ->applyFilters();
    }

    /**
     * @return QueryExport
     */
    public function export(): QueryExport
    {
        $this->buildQuery();

        return (new QueryExport($this->query))->headers($this->headers);
    }

    /**
     * Add parameters from route
     *
     * @param $key
     * @param $value
     *
     * @return void
     */
    public function addParameter($key, $value): TableResource
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
    public function newQuery(): TableResource
    {
        $this->query = QueryBuilder::for($this->model);

        return $this;
    }

    /**
     * Apply custom filters to query
     *
     * @return void
     */
    protected function applyCustomFilters(): TableResource
    {
        foreach ($this->parameters as $key => $value) {
            if (method_exists($this, 'filter'.ucfirst($key))) {
                $this->{'filter'.ucfirst($key)}($value);
            }
        }

        return $this;
    }

    /**
     * Add allowed filters to query builder
     *
     * @return $this
     */
    protected function applyFilters(): TableResource
    {
        $this->query->allowedFilters($this->filters);

        return $this;
    }

    /**
     * Apply default sort to builder
     *
     * @return $this
     */
    public function applyDefaultSort(): TableResource
    {
        if ($this->defaultSort) {
            $this->query->defaultSort($this->defaultSort);
        }

        return $this;
    }

    /**
     * Apply default sort to builder
     *
     * @return $this
     */
    public function applySorts(): TableResource
    {
        if ($this->sorts) {
            $this->query->allowedSorts($this->sorts);
        }

        return $this;
    }

    /**
     * Add global filter if it exists
     *
     * @return $this
     */
    public function applyGlobalFilter(): TableResource
    {
        if (method_exists($this, 'globalFilter')) {
            $this->filters[] = $this->globalFilter();
        }

        return $this;
    }

    public function __call(string $name, $arguments)
    {
        return $this->forwardCallTo($this->query, $name, $arguments);
    }

    protected function forwardCallTo($object, $method, $parameters)
    {
        return $object->{$method}(...$parameters);
    }
}
