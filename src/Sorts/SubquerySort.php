<?php

declare(strict_types=1);

namespace Humweb\Table\Sorts;

use Closure;
use Illuminate\Contracts\Database\Query\Builder;

class SubquerySort implements Sort
{
    /**
     * @param  Closure  $subqueryBuilder  Receives the parent Builder, returns a subquery Builder
     */
    public function __construct(protected Closure $subqueryBuilder) {}

    public function __invoke(Builder $query, bool $descending, string $property): void
    {
        $direction = $descending ? 'desc' : 'asc';

        $query->orderBy(($this->subqueryBuilder)($query), $direction);
    }
}
