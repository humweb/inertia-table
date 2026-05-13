<?php

declare(strict_types=1);

namespace Humweb\Table\Pipeline;

use Humweb\Table\TableRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class QueryPipeline
{
    /** @var list<QueryStage> */
    protected array $stages = [];

    public function through(QueryStage ...$stages): static
    {
        $this->stages = array_merge($this->stages, $stages);

        return $this;
    }

    /**
     * Replace a stage by class name. If not found, appends the new stage.
     */
    public function replace(string $stageClass, QueryStage $replacement): static
    {
        foreach ($this->stages as $index => $stage) {
            if ($stage instanceof $stageClass) {
                $this->stages[$index] = $replacement;

                return $this;
            }
        }

        $this->stages[] = $replacement;

        return $this;
    }

    /**
     * Insert a stage before another stage by class name.
     */
    public function before(string $stageClass, QueryStage $newStage): static
    {
        foreach ($this->stages as $index => $stage) {
            if ($stage instanceof $stageClass) {
                array_splice($this->stages, $index, 0, [$newStage]);

                return $this;
            }
        }

        array_unshift($this->stages, $newStage);

        return $this;
    }

    /**
     * Insert a stage after another stage by class name.
     */
    public function after(string $stageClass, QueryStage $newStage): static
    {
        foreach ($this->stages as $index => $stage) {
            if ($stage instanceof $stageClass) {
                array_splice($this->stages, $index + 1, 0, [$newStage]);

                return $this;
            }
        }

        $this->stages[] = $newStage;

        return $this;
    }

    public function process(Builder|QueryBuilder $query, TableRequest $request): Builder|QueryBuilder
    {
        $pipeline = array_reduce(
            array_reverse($this->stages),
            fn ($next, QueryStage $stage) => fn (Builder|QueryBuilder $q) => $stage->handle($q, $request, $next),
            fn (Builder|QueryBuilder $q) => $q,
        );

        return $pipeline($query);
    }

    /**
     * @return list<QueryStage>
     */
    public function getStages(): array
    {
        return $this->stages;
    }
}
