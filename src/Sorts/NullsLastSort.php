<?php

namespace Humweb\Table\Sorts;

use Illuminate\Contracts\Database\Query\Builder;

class NullsLastSort implements Sort
{
    public function __invoke(Builder $query, bool $descending, string $property)
    {
        $direction = $descending ? 'DESC' : 'ASC';

        /** @phpstan-ignore-next-line */
        if ($query->getConnection()->getDriverName() == 'pgsql') {
            $query->orderByRaw("{$property} {$direction} NULLS LAST");
        } else {
            $query->orderByRaw("{$property} IS NULL, {$property} {$direction}");
        }
    }
}
