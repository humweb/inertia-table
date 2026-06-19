<?php

declare(strict_types=1);

namespace Humweb\Table\Pipeline;

use Closure;
use Humweb\Table\Filters\FilterCollection;
use Humweb\Table\TableRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class ApplyFilters implements QueryStage
{
    public function __construct(protected FilterCollection $filters)
    {
    }

    public function handle(Builder|QueryBuilder $query, TableRequest $request, Closure $next): Builder|QueryBuilder
    {
        $this->filters->applyFromTableRequest($request, $query);

        return $next($query);
    }
}
