<?php

namespace Humweb\Table\Sorts;

use Illuminate\Database\Eloquent\Builder;

interface Sort
{
    public function __invoke(Builder $query, bool $descending, string $property);
}
