<?php

use Humweb\Table\Filters\DateRangeFilter;
use Humweb\Table\Tests\Unit\TestBuilder;
use Humweb\Table\Tests\Unit\TestConnection;
use Illuminate\Http\Request;

it('guards when dateFormat is missing', function () {
    $filter = new DateRangeFilter('created_at');

    $request = Request::createFromGlobals();
    $builder = mock(TestBuilder::class)
        ->shouldReceive('getConnection')->andReturn(new TestConnection(''))
        ->getMock();

    $filter->apply($request, $builder, '2024-01-01 - 2024-01-10');
    expect($filter->jsonSerialize()['value'])->toBeNull();
});

it('parses array inputs safely', function () {
    $filter = new DateRangeFilter('created_at');
    $filter->dateFormat = 'Y-m-d';

    $request = Request::createFromGlobals();
    $builder = mock(TestBuilder::class)
        ->shouldReceive('whereBetween')->once()->andReturnSelf()
        ->shouldReceive('getConnection')->andReturn(new TestConnection(''))
        ->getMock();

    $filter->apply($request, $builder, ['2024-01-01', '2024-01-10']);
});
