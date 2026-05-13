<?php

declare(strict_types=1);

namespace Humweb\Table\Filters;

use Humweb\Table\Contracts\FilterCollectionable;
use Humweb\Table\TableRequest;
use Humweb\Table\Validation\ValidatesCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use JsonSerializable;

class FilterCollection extends Collection implements FilterCollectionable
{
    use ValidatesCollection;

    /**
     * @param array $items
     *
     * @return FilterCollection
     */
    public static function make($items = []): FilterCollection
    {
        return new static($items);
    }

    public function apply(Request $request, $query): void
    {
        $reqFilters = $request->get('filters');
        $this->applyFilterValues($reqFilters, $request, $query);
    }

    /**
     * Apply filters using a TableRequest (key-aware) for multi-table support.
     */
    public function applyFromTableRequest(TableRequest $tableRequest, $query): void
    {
        $reqFilters = $tableRequest->getFilterParams();
        $this->applyFilterValues($reqFilters ?: null, $tableRequest->getRequest(), $query);
    }

    /**
     * @param  array<string, mixed>|null  $reqFilters
     */
    protected function applyFilterValues(?array $reqFilters, Request $request, $query): void
    {
        if (! $reqFilters) {
            return;
        }

        $this->validateFilterInput($reqFilters);
        $this->each(function (Filter $filter) use ($request, $query, $reqFilters) {
            $hasRequestValue = array_key_exists($filter->field, $reqFilters);
            $value = $hasRequestValue ? $reqFilters[$filter->field] : ($filter->value ?? null);

            if ($value === '' || $value === null) {
                return;
            }

            $filter->value = $value;
            $filter->apply($request, $query, $value);
        });
    }

    /**
     * @return array|mixed[]|\TMapValue[]
     */
    public function toArray()
    {
        return $this->map(function ($value) {
            return $value instanceof JsonSerializable ? $value->jsonSerialize() : $value;
        })->all();
    }

    /**
     * @return array|mixed[]
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
