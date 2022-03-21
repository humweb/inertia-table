<?php

namespace Humweb\Table;

use Humweb\InertiaTable\Filters\Filter;
use Humweb\Table\Fields\FieldCollection;
use Humweb\Table\Filters\FilterCollection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Inertia\Response;

class InertiaTable
{
    public Request $request;
    public FieldCollection $columns;
    public FilterCollection $filters;
    public Collection $search;
    public bool $globalSearch = true;
    public LengthAwarePaginator|null $records = null;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->columns = FieldCollection::make([]);
        $this->filters = FilterCollection::make([]);
        $this->search  = collect();
    }

    /**
     * Disable the global search.
     *
     * @return self
     */
    public function disableGlobalSearch(): InertiaTable
    {
        $this->globalSearch = false;

        return $this;
    }

    /**
     * Build main props to share with table component
     *
     * @return array
     */
    public function getQueryBuilderProps(): array
    {
        $columns = $this->transformColumns();
        $search  = $this->transformSearch();
        $filters = $this->transformFilters();

        return [
            'sort'    => $this->request->query('sort'),
            'page'    => Paginator::resolveCurrentPage(),
            'columns' => $columns->isNotEmpty() ? $columns->all() : (object) [],
            'search'  => $search->isNotEmpty() ? $search->all() : (object) [],
            'filters' => $filters->isNotEmpty() ? $filters->all() : (object) [],
        ];
    }

    /**
     * Transform the column collection for frontend.
     *
     * @return \Illuminate\Support\Collection
     */
    private function transformColumns(): Collection
    {
        $columns = explode(',', $this->request->query('hidden', ''));

        if (empty($columns)) {
            return $this->columns;
        }

        return $this->columns->map(function ($column, $key) use ($columns) {
            if (in_array($column->attribute, $columns)) {
                $column->visible(false);
            }

            return $column;
        });
    }

    /**
     * Transform the search collection for the frontend
     *
     * @return \Illuminate\Support\Collection
     */
    private function transformSearch(): Collection
    {
        $search = $this->search->collect();

        if ($this->globalSearch) {
            $search->prepend([
                'key'   => 'global',
                'label' => 'global',
                'value' => null,
            ], 'global');
        }

        $requestSearches = $this->request->query('search', []);

        if (empty($requestSearches)) {
            return $search;
        }

        $this->columns->filter(fn($f) => $f->searchable)
            ->each(function ($f) use ($requestSearches) {
                $this->searchable($f->attribute, $f->name, Arr::get($requestSearches, $f->attribute));
            });
        return $this->search;
    }

    /**
     * Transform the filters collection for frontend
     *
     * @return \Illuminate\Support\Collection
     */
    private function transformFilters(): Collection
    {
        $requestFilters = $this->request->query('filters', []);

        if (empty($requestFilters)) {
            return $this->filters;
        }

        return $this->filters->map(function ($filter) use ($requestFilters) {
            if (array_key_exists($filter->field, $requestFilters)) {
                $filter->value = $requestFilters[$filter->field];
            }

            return $filter;
        });
    }

    /**
     * Share query builder props with Inertia response.
     *
     * @param  \Inertia\Response  $response
     *
     * @return \Inertia\Response
     */
    public function shareProps(Response $response): Response
    {
        if ($this->records instanceof LengthAwarePaginator) {
            $paginated = $this->records->toArray();
            $response->with('records', $paginated['data'])
                ->with('pagination', Arr::except($paginated, 'data'));
        }
        return $response->with('queryBuilderProps', $this->getQueryBuilderProps());
    }


    public function columns(array|FieldCollection $columns = []): InertiaTable
    {
        if (!($columns instanceof FieldCollection)) {
            $columns = FieldCollection::make($columns);
        }

        $this->columns = $columns;
        return $this;
    }

    /**
     * Add a search row to the query builder.
     *
     * @param  string|array  $columns
     * @param  string|null   $label
     *
     * @return self
     */
    public function searchable(string|array $columns, string $label = null, $value = null): InertiaTable
    {
        if (is_array($columns)) {
            foreach ($columns as $id => $label) {
                $this->searchable($id, $label);
            }
        } else {
            $this->search->put($columns, [
                'key'     => $columns,
                'label'   => $label,
                'value'   => $value,
                'enabled' => !is_null($value)
            ]);
        }

        return $this;
    }

    public function filters(FilterCollection|array $filters): InertiaTable
    {
        if (is_array($filters)) {
            $filters = FilterCollection::make($filters);
        }
        $this->filters = $filters;

        return $this;
    }


    /**
     * @param  LengthAwarePaginator  $records
     *
     * @return InertiaTable
     */
    public function records(LengthAwarePaginator $records)
    {
        $this->records = $records;
        return $this;
    }
}
