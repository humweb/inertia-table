<?php

declare(strict_types=1);

namespace Humweb\Table\Pipeline;

use Closure;
use Humweb\Table\Fields\FieldCollection;
use Humweb\Table\TableRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

class ApplySearch implements QueryStage
{
    public function __construct(
        protected FieldCollection $fields,
        protected string $driver = 'pgsql',
    ) {
    }

    public function handle(Builder|QueryBuilder $query, TableRequest $request, Closure $next): Builder|QueryBuilder
    {
        $searchParams = $request->getSearchParams();

        if (! empty($searchParams)) {
            $this->fields
                ->filter(fn ($field) => $field->searchable)
                ->each(function ($field) use ($query, $searchParams) {
                    $value = $searchParams[$field->attribute] ?? null;

                    if ($value === null || $value === '') {
                        return;
                    }

                    $this->whereLike($query, $field->attribute, $value);
                });
        }

        return $next($query);
    }

    protected function whereLike(Builder|QueryBuilder $query, string $field, mixed $value): void
    {
        if (is_numeric($value)) {
            $query->where(DB::raw($field), $value);

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

        $query->where(DB::raw($field), $like, '%'.strtolower((string) $value).'%');
    }
}
