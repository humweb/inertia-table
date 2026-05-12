<?php

declare(strict_types=1);

namespace Humweb\Table\Pipeline;

use Closure;
use Humweb\Table\Fields\FieldCollection;
use Humweb\Table\TableRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

class ApplyGlobalSearch implements QueryStage
{
    protected ?Closure $customHandler = null;

    public function __construct(
        protected FieldCollection $fields,
        ?callable $customHandler = null,
    ) {
        if ($customHandler !== null) {
            $this->customHandler = $customHandler(...);
        }
    }

    public function handle(Builder|QueryBuilder $query, TableRequest $request, Closure $next): Builder|QueryBuilder
    {
        $searchParams = $request->getSearchParams();
        $globalValue = $searchParams['global'] ?? null;

        if ($globalValue === null || $globalValue === '') {
            return $next($query);
        }

        if ($this->customHandler !== null) {
            ($this->customHandler)($query, $globalValue);

            return $next($query);
        }

        $searchableFields = $this->fields->filter(fn ($f) => $f->searchable);

        if ($searchableFields->isEmpty()) {
            return $next($query);
        }

        $query->where(function ($q) use ($searchableFields, $globalValue) {
            $searchableFields->each(function ($field) use ($q, $globalValue) {
                $this->orWhereLike($q, $field->attribute, $globalValue);
            });
        });

        return $next($query);
    }

    protected function orWhereLike(Builder|QueryBuilder $query, string $field, mixed $value): void
    {
        if (is_numeric($value)) {
            $query->orWhere(DB::raw($field), $value);

            return;
        }

        $driver = $query->getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            $like = 'ilike';
        } elseif ($driver === 'sqlite') {
            $like = 'like';
        } else {
            $field = "LOWER({$field})";
            $like = 'like';
        }

        $query->orWhere(DB::raw($field), $like, '%'.strtolower((string) $value).'%');
    }
}
