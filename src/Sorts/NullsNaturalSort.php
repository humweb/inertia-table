<?php

namespace Humweb\Table\Sorts;

use Illuminate\Contracts\Database\Query\Builder;

/**
 * Sorts a nullable column so that NULL position follows the sort direction:
 *   ASC  → NULLS FIRST (e.g. active/unset records at top)
 *   DESC → NULLS LAST  (e.g. active/unset records at bottom)
 */
class NullsNaturalSort implements Sort
{
    public function __invoke(Builder $query, bool $descending, string $property)
    {
        /** @phpstan-ignore-next-line */
        if ($query->getConnection()->getDriverName() === 'pgsql') {
            $nulls = $descending ? 'NULLS LAST' : 'NULLS FIRST';
            $direction = $descending ? 'DESC' : 'ASC';
            $query->orderByRaw("{$property} {$direction} {$nulls}");
        } else {
            if ($descending) {
                $query->orderByRaw("{$property} IS NULL, {$property} DESC");
            } else {
                $query->orderByRaw("{$property} IS NOT NULL, {$property} ASC");
            }
        }
    }
}
