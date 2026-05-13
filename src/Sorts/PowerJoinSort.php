<?php

declare(strict_types=1);

namespace Humweb\Table\Sorts;

use Illuminate\Contracts\Database\Query\Builder;

class PowerJoinSort implements Sort
{
    /**
     * @param  string  $relation  Dot-notation relation path (e.g. 'author.address')
     * @param  string  $column    Column to sort by on the final relation
     */
    public function __construct(
        protected string $relation,
        protected string $column,
    ) {
    }

    public function __invoke(Builder $query, bool $descending, string $property): void
    {
        $direction = $descending ? 'desc' : 'asc';
        $path = "{$this->relation}.{$this->column}";

        $query->orderByPowerJoins($path, $direction);
    }
}
