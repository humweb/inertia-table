<?php

declare(strict_types=1);

namespace Humweb\Table\Pipeline;

use Closure;
use Humweb\Table\Sorts\Sort;
use Humweb\Table\TableRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class ApplyDefaultSort implements QueryStage
{
    protected ?Closure $defaultSortCallback = null;

    public function __construct(
        protected string|Sort $defaultSort = 'id',
        ?callable $defaultSortCallback = null,
    ) {
        if ($defaultSortCallback !== null) {
            $this->defaultSortCallback = $defaultSortCallback(...);
        }
    }

    public function handle(Builder|QueryBuilder $query, TableRequest $request, Closure $next): Builder|QueryBuilder
    {
        if (! $request->has('sort')) {
            if ($this->defaultSortCallback !== null) {
                ($this->defaultSortCallback)($query);
            } elseif ($this->defaultSort instanceof Sort) {
                ($this->defaultSort)($query, false, '');
            } else {
                $descending = str_starts_with($this->defaultSort, '-');
                $column = ltrim($this->defaultSort, '-');
                $query->orderBy($column, $descending ? 'desc' : 'asc');
            }
        }

        return $next($query);
    }
}
