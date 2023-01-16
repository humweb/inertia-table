<?php

namespace Humweb\Table\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait HasResourceQueries
{
    /**
     * @var Builder
     */
    protected Builder $query;

    /**
     * @var array
     */
    protected array $sorts;

    public function getSelectData()
    {
        return $this->query->get($this->title, 'id')
            ->map(fn ($row) => [
                'id' => $row->id,
                'label' => $row->{$this->title},
            ]);
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

        $data = $this->query->fastPaginate($perPage, $columns, $pageName, $page)->withQueryString();

        if ($this->runtimeTransform) {
            $data = $data->through($this->runtimeTransform);
        } elseif (method_exists($this, 'transform')) {
            $data = $data->through($this->transform());
        }

        return $data;
    }

    public function buildQuery()
    {
        $this->applyDefaultSort()
            ->applyEagerLoads()
            ->applySorts()
            ->applyGlobalFilter()
            ->applyCustomFilters()
            ->applySearch()
            ->applyFilters();
    }

    public function applySearch()
    {
        $searchParams = $this->request->get('search');

        if ($searchParams) {
            $this->getFields()->filter(fn ($fld) => $fld->searchable)->each(function ($field) use ($searchParams) {
                if (isset($searchParams[$field->attribute]) && ! empty($searchParams[$field->attribute])) {
                    $this->whereLike($field->attribute, $searchParams[$field->attribute]);
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
            $like = 'like';
        }

        $this->query->where(DB::raw($field), $like, '%'.strtolower($value).'%');
    }

    /**
     * Create new query builder instance
     *
     * @return $this
     */
    public function newQuery(): static
    {
        $this->query = $this->model::query();
        $this->driver = $this->query->getConnection()->getDriverName();

        return $this;
    }

    /**
     * Applies eager load relationships to query
     *
     * @return $this
     */
    public function applyEagerLoads()
    {
        if (! empty($this->with)) {
            $this->query->with($this->with);
        }

        return $this;
    }

    /**
     * Apply custom filters to query
     *
     * @return $this
     */
    public function applyCustomFilters(): static
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
    protected function applyFilters(): static
    {
        $this->getFilters()->apply($this->request, $this->query);


        return $this;
    }

    /**
     * Apply default sort to builder
     *
     * @return $this
     */
    public function applyDefaultSort(): static
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
    public function applySorts(): static
    {
        if ($this->request->has('sort')) {
            $sortField = $this->request->get('sort');
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
    public function applyGlobalFilter(): static
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
}
