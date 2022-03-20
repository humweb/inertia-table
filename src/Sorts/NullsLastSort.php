<?php

namespace Humweb\InertiaTable\Sorts;

use Illuminate\Database\Eloquent\Builder;

class NullsLastSort implements Sort
{
    public function __invoke(Builder $query, bool $descending, string $property)
    {
        $direction = $descending ? 'DESC' : 'ASC';

        if ($query->getConnection()->getDriverName() == 'pgsql') {
            $query->orderByRaw("{$property} {$direction} NULLS LAST");
        } else {
            $query->orderByRaw("{$property} IS NULL, {$property} {$direction}");
        }
    }
}
