<?php

declare(strict_types=1);

namespace Humweb\Table\Concerns;

use Humweb\Table\Pipeline\ApplyCustomFilters;
use Humweb\Table\Pipeline\ApplyDefaultSort;
use Humweb\Table\Pipeline\ApplyEagerLoads;
use Humweb\Table\Pipeline\ApplyFilters;
use Humweb\Table\Pipeline\ApplyGlobalSearch;
use Humweb\Table\Pipeline\ApplySearch;
use Humweb\Table\Pipeline\ApplySorts;
use Humweb\Table\Pipeline\QueryPipeline;
use Humweb\Table\Sorts\BasicCollectionSort;
use Humweb\Table\Sorts\SortMode;
use Humweb\Table\TableRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;

trait HasResourceQueries
{
    protected Builder|QueryBuilder $query;

    /** @var array<int, string> */
    protected array $with = [];

    protected ?TableRequest $tableRequest = null;

    public function setTableRequest(TableRequest $tableRequest): static
    {
        $this->tableRequest = $tableRequest;

        return $this;
    }

    public function getSelectData(): mixed
    {
        return $this->query->get(['title', 'id'])
            ->map(fn ($row) => [
                'id' => $row->id,
                'label' => $row->{$this->title},
            ]);
    }

    /**
     * @return LengthAwarePaginator
     */
    public function paginate(?int $perPage = null, array $columns = ['*'], string $pageName = 'page', ?int $page = null): LengthAwarePaginator
    {
        $this->buildQuery();

        $defaultPerPage = (int) config('inertia-table.pagination.default_per_page', 15);
        $maxPerPage = (int) config('inertia-table.pagination.max_per_page', 100);
        $perPage = (int) ($perPage ?? $defaultPerPage);

        if ($perPage > $maxPerPage) {
            $perPage = $maxPerPage;
        }

        if ($this->findActiveCollectionSortField()) {
            return $this->paginateWithCollectionSort($perPage, $columns, $pageName, $page);
        }

        $data = $this->query->paginate($perPage, $columns, $pageName, $page)->withQueryString();

        // Resource/runtime transforms run first so their closures receive the
        // real Eloquent model (typed callbacks like fn (Game $g) => ... work).
        // Field-level transforms then decorate the resulting rows.
        if ($this->runtimeTransform) {
            $data = $data->through($this->runtimeTransform);
        } elseif (method_exists($this, 'transform')) {
            $data = $data->through($this->transform());
        }

        $data = $this->getFields()->applyTransform($data);

        return $data;
    }

    public function buildQuery(): void
    {
        $pipeline = $this->buildPipeline();
        $tableRequest = $this->resolveTableRequest();

        $this->query = $pipeline->process($this->query, $tableRequest);
    }

    /**
     * Build the default query pipeline. Override in resource subclasses for customization.
     */
    protected function buildPipeline(): QueryPipeline
    {
        $pipeline = new QueryPipeline();

        $globalSearchHandler = method_exists($this, 'globalFilter')
            ? fn ($query, $value) => $this->globalFilter($query, $value)
            : null;

        $pipeline->through(
            new ApplyEagerLoads($this->with),
            new ApplyDefaultSort(
                $this->defaultSort,
                method_exists($this, 'defaultSort') ? fn ($query) => $this->defaultSort($query) : null,
            ),
            new ApplySorts($this->getFields()),
            new ApplyGlobalSearch($this->getFields(), $globalSearchHandler),
            new ApplyCustomFilters($this->parameters, $this),
            new ApplySearch($this->getFields()),
            new ApplyFilters($this->getFilters()),
        );

        if (method_exists($this, 'pipeline')) {
            $pipeline = $this->pipeline($pipeline);
        }

        return $pipeline;
    }

    protected function resolveTableRequest(): TableRequest
    {
        if ($this->tableRequest !== null) {
            return $this->tableRequest;
        }

        return new TableRequest($this->request);
    }

    public function newQuery(): static
    {
        $this->query = $this->model::query();

        return $this;
    }

    protected function findActiveCollectionSortField(): ?\Humweb\Table\Fields\Field
    {
        $tableRequest = $this->resolveTableRequest();

        if (! $tableRequest->has('sort')) {
            return null;
        }

        $sortField = $tableRequest->getSortParam();
        $rawField = ltrim($sortField, '-');

        return $this->getFields()->first(function ($field) use ($rawField) {
            return $field->attribute === $rawField
                && $field->sortable
                && $field->sortMode === SortMode::Collection;
        });
    }

    protected function paginateWithCollectionSort(int $perPage, array $columns = ['*'], string $pageName = 'page', ?int $page = null): LengthAwarePaginator
    {
        $tableRequest = $this->resolveTableRequest();
        $sortField = $tableRequest->getSortParam();
        $descending = str_starts_with($sortField, '-');
        $attribute = ltrim($sortField, '-');

        $field = $this->findActiveCollectionSortField();

        $allRecords = $this->query->get($columns);

        // Resource/runtime transform first so its closure receives the real
        // Eloquent model; field-level transforms then decorate the rows.
        if ($this->runtimeTransform) {
            $allRecords = $allRecords->map($this->runtimeTransform);
        } elseif (method_exists($this, 'transform')) {
            $allRecords = $allRecords->map($this->transform());
        }

        $toRow = fn ($record) => is_array($record)
            ? $record
            : (is_object($record) && method_exists($record, 'toArray') ? $record->toArray() : (array) $record);

        $transformableFields = $this->getFields()->filter(fn ($f) => $f->hasTransform() || $f->hasCallableTransform());
        if ($transformableFields->isNotEmpty()) {
            $allRecords = $allRecords->map(function ($record) use ($transformableFields, $toRow) {
                $record = $toRow($record);
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
            $allRecords = $allRecords->map($toRow);
        }

        if ($field->collectionSortStrategy) {
            $allRecords = ($field->collectionSortStrategy)($allRecords, $descending, $attribute);
        } else {
            $allRecords = (new BasicCollectionSort())($allRecords, $descending, $attribute);
        }

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

    public function hasGlobalFilter(): bool
    {
        return method_exists($this, 'globalFilter');
    }

    public function getQuery(): Builder|QueryBuilder
    {
        return $this->query;
    }
}
