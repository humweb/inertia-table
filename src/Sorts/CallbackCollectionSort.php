<?php

namespace Humweb\Table\Sorts;

use Closure;
use Illuminate\Support\Collection;

class CallbackCollectionSort implements CollectionSort
{
    public function __construct(private Closure $callback) {}

    public function __invoke(Collection $collection, bool $descending, string $property): Collection
    {
        return call_user_func($this->callback, $collection, $descending, $property);
    }
}
