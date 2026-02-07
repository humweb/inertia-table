<?php

namespace Humweb\Table\Concerns;

use Humweb\Table\Sorts\BasicCollectionSort;
use Humweb\Table\Sorts\SortMode;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
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
        return $this->query->get(['title', 'id'])
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

        $defaultPerPage = (int) config('inertia-table.pagination.default_per_page', 15);
        $maxPerPage = (int) config('inertia-table.pagination.max_per_page', 100);
        $perPage = (int) ($perPage ?? $defaultPerPage);
        if ($perPage > $maxPerPage) {
            $perPage = $maxPerPage;
        }

        // Collection-sort fields require fetching all records, sorting, then paginating manually
        if ($this->findActiveCollectionSortField()) {
            return $this->paginateWithCollectionSort($perPage, $columns, $pageName, $page);
        }

        $data = $this->query->paginate($perPage, $columns, $pageName, $page)->withQueryString();

        $data = $this->getFields()->applyTransform($data);

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
        if (is_numeric($value)) {
            $this->query->where(DB::raw($field), $value);

            return $this;
        }
        if ($this->driver == 'pgsql') {
            $like = 'ilike';
        } elseif ($this->driver == 'sqlite') {
            $like = 'like';
        } else {
            $field = "LOWER({$field})";
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
     * Apply query-based sorts to the builder (skips Collection and Client sort modes).
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
                if ($field->attribute == $sortField && $field->sortable && $field->sortMode === SortMode::Query) {
                    ($field->sortableStrategy)($this->query, $descending, $sortField);
                }
            });
        }

        return $this;
    }

    /**
     * Find the active collection-sort field, if any.
     *
     * @return \Humweb\Table\Fields\Field|null
     */
    protected function findActiveCollectionSortField(): ?\Humweb\Table\Fields\Field
    {
        if (! $this->request->has('sort')) {
            return null;
        }

        $sortField = $this->request->get('sort');
        $rawField = ltrim($sortField, '-');

        return $this->getFields()->first(function ($field) use ($rawField) {
            return $field->attribute === $rawField
                && $field->sortable
                && $field->sortMode === SortMode::Collection;
        });
    }

    /**
     * Apply collection-based sorting to a paginated result.
     *
     * Fetches all matching records, applies transforms, sorts, then manually paginates.
     *
     * @param  int     $perPage
     * @param  array   $columns
     * @param  string  $pageName
     * @param  int|null $page
     *
     * @return LengthAwarePaginator
     */
    protected function paginateWithCollectionSort(int $perPage, array $columns = ['*'], string $pageName = 'page', ?int $page = null): LengthAwarePaginator
    {
        $sortField = $this->request->get('sort');
        $descending = str_starts_with($sortField, '-');
        $attribute = ltrim($sortField, '-');

        $field = $this->findActiveCollectionSortField();

        $allRecords = $this->query->get($columns);

        // Apply field transforms
        $transformableFields = $this->getFields()->filter(fn ($f) => $f->hasTransform() || $f->hasCallableTransform());
        if ($transformableFields->isNotEmpty()) {
            $allRecords = $allRecords->map(function ($record) use ($transformableFields) {
                $record = $record->toArray();
                foreach ($transformableFields as $f) {
                    $value = data_get($record, $f->attribute);
                    if ($f->hasTransform()) {
                        $value = $f->transform($value);
                    } elseif ($f->hasCallableTransform()) {
                        $value = ($f->callableTransform)($value);
                    }
                    data_set($record, $f->attribute, $value);
                }

                return $record;
            });
        } else {
            $allRecords = $allRecords->map(fn ($record) => $record->toArray());
        }

        // Apply runtime transform
        if ($this->runtimeTransform) {
            $allRecords = $allRecords->map($this->runtimeTransform);
        } elseif (method_exists($this, 'transform')) {
            $allRecords = $allRecords->map($this->transform());
        }

        // Apply collection sort
        if ($field->collectionSortStrategy) {
            $allRecords = ($field->collectionSortStrategy)($allRecords, $descending, $attribute);
        } else {
            $allRecords = (new BasicCollectionSort())($allRecords, $descending, $attribute);
        }

        // Manually paginate
        $currentPage = $page ?? LengthAwarePaginator::resolveCurrentPage($pageName);
        $sliced = $allRecords->slice(($currentPage - 1) * $perPage, $perPage)->values();

        return (new LengthAwarePaginator(
            $sliced,
            $allRecords->count(),
            $perPage,
            $currentPage,
            ['pageName' => $pageName],
        ))->withQueryString();
    }

    /**
     * Add global filter if it exists
     *
     * @return $this
     */
    public function applyGlobalFilter(): static
    {
        if (! $this->requestHasGlobalFilter()) {
            return $this;
        }

        $global = $this->request->get('search')['global'];

        if (method_exists($this, 'globalFilter')) {
            $this->globalFilter($this->query, $global);

            return $this;
        }

        // Default global search: apply across searchable fields
        $this->getFields()->filter(fn ($f) => $f->searchable)
            ->each(function ($field) use ($global) {
                $this->whereLike($field->attribute, $global);
            });

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
