<?php

namespace Humweb\Table\Sorts;

use Illuminate\Support\Collection;

interface CollectionSort
{
    public function __invoke(Collection $collection, bool $descending, string $property): Collection;
}
