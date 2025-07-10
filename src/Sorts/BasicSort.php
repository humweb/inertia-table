<?php

namespace Humweb\Table\Sorts;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Str;

class BasicSort implements Sort
{
    public function __invoke(Builder $query, bool $descending, string $property)
    {
        $direction = $descending ? 'desc' : 'asc';

        if (Str::contains($property, '.')) {
            $query->orderByPowerJoins($property, $direction);
        } else {
            $query->orderBy($property, $direction);
        }
    }
}
