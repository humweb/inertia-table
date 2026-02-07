<?php

namespace Humweb\Table\Sorts;

use Illuminate\Support\Collection;

class BasicCollectionSort implements CollectionSort
{
    public function __construct(public SortType $type = SortType::Auto) {}

    public function __invoke(Collection $collection, bool $descending, string $property): Collection
    {
        return $collection->sortBy(function ($item) use ($property) {
            $value = data_get($item, $property);

            return match ($this->resolveType($value)) {
                SortType::Integer => (int) $value,
                SortType::Date => strtotime((string) $value) ?: 0,
                default => mb_strtolower((string) ($value ?? '')),
            };
        }, SORT_REGULAR, $descending)->values();
    }

    /**
     * Resolve the sort type for a given value.
     */
    private function resolveType(mixed $value): SortType
    {
        if ($this->type !== SortType::Auto) {
            return $this->type;
        }

        if (is_int($value) || is_float($value)) {
            return SortType::Integer;
        }

        if (is_string($value)) {
            if (preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
                return SortType::Date;
            }

            if (is_numeric($value)) {
                return SortType::Integer;
            }
        }

        return SortType::String;
    }
}
