<?php

namespace Humweb\Table\Sorts;

use Illuminate\Contracts\Database\Query\Builder;

class CallbackSort implements Sort
{
    private $callback;

    public function __construct($callback)
    {
        $this->callback = $callback;
    }

    /** {@inheritdoc} */
    public function __invoke(Builder $query, bool $descending, string $property)
    {
        return call_user_func($this->callback, $query, $descending, $property);
    }
}
