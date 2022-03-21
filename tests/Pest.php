<?php

use Humweb\Table\Tests\TestCase;
use Illuminate\Support\Facades\DB;

uses(TestCase::class)->in(__DIR__);


function assertQueryExecuted(string $query)
{
    $queries = array_map(function ($queryLogItem) {
        return $queryLogItem['query'];
    }, DB::getQueryLog());

    expect($queries)->toContain($query);
}
