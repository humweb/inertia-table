<?php

declare(strict_types=1);

namespace Humweb\Table\Pipeline;

use Closure;
use Humweb\Table\TableRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class ApplyEagerLoads implements QueryStage
{
    /**
     * @param  array<int, string>  $relations
     */
    public function __construct(protected array $relations = [])
    {
    }

    public function handle(Builder|QueryBuilder $query, TableRequest $request, Closure $next): Builder|QueryBuilder
    {
        if (! empty($this->relations) && $query instanceof Builder) {
            $query->with($this->relations);
        }

        return $next($query);
    }
}
