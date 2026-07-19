<?php

declare(strict_types=1);

namespace Humweb\Table;

use Humweb\Table\Fields\FieldCollection;
use Humweb\Table\Filters\FilterCollection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class InertiaTable
{
    public FieldCollection $columns;

    public FilterCollection $filters;

    public Collection $search;

    public bool $globalSearch = true;

    public ?LengthAwarePaginator $records = null;

    public function __construct(
        public TableRequest $tableRequest,
    ) {
        $this->columns = new FieldCollection();
        $this->filters = new FilterCollection();
        $this->search = collect();
    }

    public function globalSearch(bool $bool): static
    {
        $this->globalSearch = $bool;

        return $this;
    }

    /**
     * Build the props payload for a single table.
     *
     * @return array<string, mixed>
     */
    public function buildTableProps(): array
    {
        $columns = $this->flagVisibility();
        $search = $this->transformSearch();
        $filters = $this->transformFilters();

        $defaultPerPage = (int) config('inertia-table.pagination.default_per_page', 15);

        return [
            'sort' => $this->tableRequest->getSortParam(),
            'page' => $this->tableRequest->getPage(),
            'perPage' => $this->tableRequest->getPerPage($defaultPerPage),
            'columns' => $columns->isNotEmpty() ? $columns->all() : (object) [],
            'search' => $search->isNotEmpty() ? $search->all() : (object) [],
            'filters' => $filters->isNotEmpty() ? $filters->all() : (object) [],
        ];
    }

    /**
     * Build the full resolved payload for this table (records + pagination + tableProps).
     *
     * @return array<string, mixed>
     */
    public function resolve(): array
    {
        $result = [
            'tableProps' => $this->buildTableProps(),
        ];

        if ($this->records instanceof LengthAwarePaginator) {
            $paginated = $this->records->toArray();
            $result['records'] = $this->resolveRouteUrls($paginated['data']);
            $result['pagination'] = Arr::except($paginated, 'data');
        }

        return $result;
    }

    /**
     * Resolve per-row URLs for action/link/relation columns server-side so the
     * frontend never needs to know route names. Resolved URLs are attached to
     * each record under `__hrefs[attribute]` (link/relation) and
     * `__actions[attribute]` (an array of {label, url, method, class}).
     *
     * @param  array<int, array<string, mixed>>  $records
     * @return array<int, array<string, mixed>>
     */
    private function resolveRouteUrls(array $records): array
    {
        $routedColumns = $this->columns->filter(
            fn ($column) => isset($column->meta['route']) || isset($column->meta['actions'])
        );

        if ($routedColumns->isEmpty()) {
            return $records;
        }

        return array_map(function (array $record) use ($routedColumns) {
            $hrefs = [];
            $actions = [];

            foreach ($routedColumns as $column) {
                if (isset($column->meta['actions'])) {
                    $actions[$column->attribute] = array_map(fn (array $action) => [
                        'label' => $action['label'] ?? null,
                        'url' => $this->resolveRouteUrl($action['route'] ?? null, $action['params'] ?? [], $record),
                        'method' => $action['method'] ?? 'get',
                        'class' => $action['class'] ?? null,
                    ], $column->meta['actions']);
                } elseif (isset($column->meta['route'])) {
                    $hrefs[$column->attribute] = $this->resolveRouteUrl(
                        $column->meta['route'],
                        $column->meta['routeParams'] ?? [],
                        $record
                    );
                }
            }

            if ($hrefs !== []) {
                $record['__hrefs'] = $hrefs;
            }

            if ($actions !== []) {
                $record['__actions'] = $actions;
            }

            return $record;
        }, $records);
    }

    /**
     * Resolve a single route name + record-keyed params to a URL, returning null
     * when the route cannot be resolved (rather than throwing for one bad row).
     *
     * @param  array<int, string>  $paramKeys
     * @param  array<string, mixed>  $record
     */
    private function resolveRouteUrl(?string $name, array $paramKeys, array $record): ?string
    {
        if ($name === null || $name === '') {
            return null;
        }

        try {
            return route($name, array_map(fn ($key) => $record[$key] ?? null, $paramKeys));
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function flagVisibility(): Collection
    {
        $hidden = $this->tableRequest->getHiddenColumns();
        $columns = $hidden !== '' ? explode(',', $hidden) : [];

        if (empty($columns)) {
            return $this->columns;
        }

        return $this->columns->map(function ($column) use ($columns) {
            if (in_array($column->attribute, $columns)) {
                $column->visible(false);
            }

            return $column;
        });
    }

    private function transformSearch(): Collection
    {
        $requestSearches = $this->tableRequest->getSearchParams();

        if ($this->globalSearch) {
            $this->searchable('global', 'Search..', Arr::get($requestSearches, 'global'));
        }

        $this->columns->filter(fn ($f) => $f->searchable)
            ->each(function ($f) use ($requestSearches) {
                $this->searchable($f->attribute, $f->name, Arr::get($requestSearches, $f->attribute));
            });

        return $this->search;
    }

    private function transformFilters(): Collection
    {
        $requestFilters = $this->tableRequest->getFilterParams();

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

    public function columns(array|FieldCollection $columns = []): static
    {
        if (! ($columns instanceof FieldCollection)) {
            $columns = new FieldCollection($columns);
        }

        $this->columns = $columns;

        return $this;
    }

    public function searchable(string|array $columns, ?string $label = null, mixed $value = null): static
    {
        if (is_array($columns)) {
            foreach ($columns as $id => $label) {
                $this->searchable($id, $label);
            }
        } else {
            $this->search->put($columns, [
                'key' => $columns,
                'label' => $label,
                'value' => $value,
                'enabled' => ! is_null($value),
            ]);
        }

        return $this;
    }

    public function filters(FilterCollection|array $filters): static
    {
        if (is_array($filters)) {
            $filters = new FilterCollection($filters);
        }

        $this->filters = $filters;

        return $this;
    }

    public function records(LengthAwarePaginator $records): static
    {
        $this->records = $records;

        return $this;
    }

    public function getTableRequest(): TableRequest
    {
        return $this->tableRequest;
    }
}
