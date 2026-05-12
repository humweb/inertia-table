<?php

declare(strict_types=1);

namespace Humweb\Table\Pipeline;

use Closure;
use Humweb\Table\Fields\FieldCollection;
use Humweb\Table\Sorts\SortMode;
use Humweb\Table\TableRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class ApplySorts implements QueryStage
{
    public function __construct(protected FieldCollection $fields) {}

    public function handle(Builder|QueryBuilder $query, TableRequest $request, Closure $next): Builder|QueryBuilder
    {
        $sortParam = $request->getSortParam();

        if ($sortParam !== null) {
            $descending = str_starts_with($sortParam, '-');
            $sortAttribute = ltrim($sortParam, '-');

            $this->fields->each(function ($field) use ($query, $sortAttribute, $descending) {
                if ($field->attribute !== $sortAttribute || ! $field->sortable || $field->sortMode !== SortMode::Query) {
                    return;
                }

                $sortColumn = $field->sortField ?? $sortAttribute;
                ($field->sortableStrategy)($query, $descending, $sortColumn);
            });
        }

        return $next($query);
    }
}
