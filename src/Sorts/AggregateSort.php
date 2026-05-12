<?php

declare(strict_types=1);

namespace Humweb\Table\Sorts;

use Illuminate\Contracts\Database\Query\Builder;

class AggregateSort implements Sort
{
    /**
     * @param  string       $relation   Eloquent relationship name (e.g. 'posts', 'orders')
     * @param  string       $function   Aggregate function: 'count', 'sum', 'avg', 'min', 'max'
     * @param  string|null  $column     Column for sum/avg/min/max (null for count)
     */
    public function __construct(
        protected string $relation,
        protected string $function = 'count',
        protected ?string $column = null,
    ) {}

    public function __invoke(Builder $query, bool $descending, string $property): void
    {
        $direction = $descending ? 'desc' : 'asc';

        $aliasColumn = match ($this->function) {
            'count' => "{$this->relation}_count",
            'sum' => "{$this->relation}_sum_{$this->column}",
            'avg' => "{$this->relation}_avg_{$this->column}",
            'min' => "{$this->relation}_min_{$this->column}",
            'max' => "{$this->relation}_max_{$this->column}",
            default => $property,
        };

        $existingAggregates = collect($query->getQuery()->columns ?? [])
            ->map(fn ($col) => is_string($col) ? $col : '')
            ->filter(fn ($col) => str_contains($col, $aliasColumn));

        if ($existingAggregates->isEmpty()) {
            match ($this->function) {
                'count' => $query->withCount($this->relation),
                'sum' => $query->withSum($this->relation, $this->column),
                'avg' => $query->withAvg($this->relation, $this->column),
                'min' => $query->withMin($this->relation, $this->column),
                'max' => $query->withMax($this->relation, $this->column),
                default => null,
            };
        }

        $query->orderBy($aliasColumn, $direction);
    }
}
