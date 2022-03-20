<?php

namespace Humweb\Table;


use Humweb\Table\Filters\FilterCollection;
use Humweb\Table\Traits\Makeable;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

abstract class Resource
{
    use Makeable;

    protected array $parameters = [];

    /**
     * @var FilterCollection
     */
    protected FilterCollection $filters;
    protected $query;
    protected array $sorts;
    protected string|Sort $defaultSort = 'id';
    protected $model;
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->newQuery();
        $this->request = $request;
//        $this->parameters = $parameters;
        $this->filters = new FilterCollection();
    }

    /**
     * Add filter for allowed filters
     *
     * @param $filter
     *
     * @return $this
     */
    public function addFilter($filter): Resource
    {
        if (is_array($filter)) {
            $this->filters = $this->filters->merge($filter);
        } else {
            $this->filters->push($filter);
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
            ->applySearch()
            ->applyFilters();
    }

    public function applySearch()
    {
        $reqSearch = $this->request->get('search');
        if ($reqSearch) {
            $this->fields()->filter(fn($f) => $f->searchable)->each(function ($field) use ($reqSearch) {
                if (isset($reqSearch[$field->attribute]) && !empty($reqSearch[$field->attribute])) {
                    $this->whereLike($field->attribute, $reqSearch[$field->attribute]);
                }
            });
        }
        return $this;
    }

    public function whereLike($field, $value)
    {
        if ($this->query->getConnection()->getDriverName() == 'pgsql') {
            $field = $field;
            $like  = 'ilike';
        } else {
            $field = "LOWER('{$field}')";
            $like  = 'like';
        }

        $this->query->where(DB::raw($field), $like, '%'.strtolower($value).'%');
    }

    /**
     * Add parameters from route
     *
     * @param $key
     * @param $value
     *
     * @return void
     */
    public function addParameter($key, $value): Resource
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
        $this->query = $this->model::query();

        return $this;
    }

    /**
     * Apply custom filters to query
     *
     * @return void
     */
    protected function applyCustomFilters(): Resource
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
    protected function applyFilters(): Resource
    {
        $this->filters()->apply($this->request, $this->query);


        return $this;
    }

    /**
     * Apply default sort to builder
     *
     * @return $this
     */
    public function applyDefaultSort(): Resource
    {
        if ($this->defaultSort && method_exists($this, 'defaultSort')) {
            $this->defaultSort($this->defaultSort);
        } else {
            if (is_string($this->defaultSort) && !$this->request->has('sort')) {
                $this->request->merge(['sort' => $this->defaultSort]);
            }
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

            $this->fields()->each(function ($field) use ($sortField, $descending) {
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

    public function toResponse(InertiaTable $table)
    {
        $fields = $this->fields();
        $table->columns($fields)
            ->filters($this->filters())
            ->records($this->paginate());
        $fields->filter(fn($f) => $f->searchable)
            ->each(function ($f) use ($table) {
                $table->searchable($f->attribute, $f->name, Arr::get($this->request->get('search', []), $f->attribute));
            })->all();

        return $table;
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
