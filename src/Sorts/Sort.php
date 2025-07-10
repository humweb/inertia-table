<?php

namespace Humweb\Table\Sorts;

use Illuminate\Contracts\Database\Query\Builder;

interface Sort
{
    public function __invoke(Builder $query, bool $descending, string $property);
}
