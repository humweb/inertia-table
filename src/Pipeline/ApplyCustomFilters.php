<?php

declare(strict_types=1);

namespace Humweb\Table\Pipeline;

use Closure;
use Humweb\Table\TableRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Str;

class ApplyCustomFilters implements QueryStage
{
    /**
     * @param  array<string, mixed>  $parameters
     * @param  object  $resource  The resource instance that defines filter* methods
     */
    public function __construct(
        protected array $parameters,
        protected object $resource,
    ) {}

    public function handle(Builder|QueryBuilder $query, TableRequest $request, Closure $next): Builder|QueryBuilder
    {
        foreach ($this->parameters as $key => $value) {
            $method = 'filter'.Str::studly(str_replace('.', '_', $key));

            if (method_exists($this->resource, $method)) {
                $this->resource->{$method}($value);
            }
        }

        return $next($query);
    }
}
