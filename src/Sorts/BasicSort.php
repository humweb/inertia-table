<?php

namespace Humweb\Table\Sorts;

use Illuminate\Database\Eloquent\Builder;

class BasicSort implements Sort
{
    public function __invoke(Builder $query, bool $descending, string $property)
    {
        $query->orderBy($property, $descending ? 'desc' : 'asc');
    }
}
