<?php

declare(strict_types=1);

use Humweb\Table\Pipeline\ApplyDefaultSort;
use Humweb\Table\Pipeline\ApplyEagerLoads;
use Humweb\Table\Pipeline\QueryPipeline;
use Humweb\Table\Pipeline\QueryStage;
use Humweb\Table\TableRequest;
use Humweb\Table\Tests\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

it('processes stages in order', function () {
    $request = new TableRequest(Request::createFromGlobals());
    $query = User::query();
    $order = [];

    $stageA = new class ($order) implements QueryStage {
        public function __construct(private array &$order)
        {
        }

        public function handle(Builder $query, TableRequest $request, Closure $next): Builder
        {
            $this->order[] = 'A';

            return $next($query);
        }
    };

    $stageB = new class ($order) implements QueryStage {
        public function __construct(private array &$order)
        {
        }

        public function handle(Builder $query, TableRequest $request, Closure $next): Builder
        {
            $this->order[] = 'B';

            return $next($query);
        }
    };

    $pipeline = new QueryPipeline();
    $pipeline->through($stageA, $stageB);
    $pipeline->process($query, $request);

    expect($order)->toBe(['A', 'B']);
});

it('can replace a stage by class name', function () {
    $pipeline = new QueryPipeline();
    $pipeline->through(new ApplyDefaultSort('id'));

    $replacement = new ApplyDefaultSort('name');
    $pipeline->replace(ApplyDefaultSort::class, $replacement);

    $stages = $pipeline->getStages();
    expect($stages)->toHaveCount(1);
    expect($stages[0])->toBe($replacement);
});

it('can insert a stage before another', function () {
    $pipeline = new QueryPipeline();
    $sort = new ApplyDefaultSort('id');
    $pipeline->through($sort);

    $eagerLoads = new ApplyEagerLoads(['posts']);
    $pipeline->before(ApplyDefaultSort::class, $eagerLoads);

    $stages = $pipeline->getStages();
    expect($stages[0])->toBe($eagerLoads);
    expect($stages[1])->toBe($sort);
});

it('can insert a stage after another', function () {
    $pipeline = new QueryPipeline();
    $sort = new ApplyDefaultSort('id');
    $pipeline->through($sort);

    $eagerLoads = new ApplyEagerLoads(['posts']);
    $pipeline->after(ApplyDefaultSort::class, $eagerLoads);

    $stages = $pipeline->getStages();
    expect($stages[0])->toBe($sort);
    expect($stages[1])->toBe($eagerLoads);
});

it('applies default sort when no sort param', function () {
    DB::enableQueryLog();

    $request = new TableRequest(Request::createFromGlobals());
    $query = User::query();

    $pipeline = new QueryPipeline();
    $pipeline->through(new ApplyDefaultSort('name'));
    $pipeline->process($query, $request);

    $sql = $query->toSql();
    expect($sql)->toContain('order by "name" asc');
});

it('skips default sort when sort param exists', function () {
    $httpRequest = Request::createFromGlobals();
    $httpRequest->query->set('sort', '-email');
    $request = new TableRequest($httpRequest);
    $query = User::query();

    $pipeline = new QueryPipeline();
    $pipeline->through(new ApplyDefaultSort('name'));
    $pipeline->process($query, $request);

    $sql = $query->toSql();
    expect($sql)->not->toContain('order by');
});
