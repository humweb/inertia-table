<?php

use Humweb\Table\Filters\TrashedFilter;
use Humweb\Table\Tests\Unit\TestBuilder;
use Humweb\Table\Tests\Unit\TestConnection;
use Illuminate\Http\Request;

it('can apply trashed filter', function () {
    $filter = TrashedFilter::make('email', 'Email');

    $request = Request::createFromGlobals();

    $mockedBuilder = mock(TestBuilder::class)
        ->shouldReceive('hasMacro')->once()->andReturn(true)
        ->shouldReceive('withTrashed')->once()->andReturnSelf()
        ->shouldReceive('getConnection')->andReturn(new TestConnection(''))
        ->getMock();

    $filter->apply($request, $mockedBuilder, 'with');

    $mockedBuilder = mock(TestBuilder::class)
        ->shouldReceive('hasMacro')->once()->andReturn(true)
        ->shouldReceive('onlyTrashed')->once()->andReturnSelf()
        ->shouldReceive('getConnection')->andReturn(new TestConnection(''))
        ->getMock();

    $filter->apply($request, $mockedBuilder, 'only');
});
