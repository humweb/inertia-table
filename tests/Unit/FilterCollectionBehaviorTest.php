<?php

use Humweb\Table\Filters\FilterCollection;
use Humweb\Table\Filters\TextFilter;
use Humweb\Table\Tests\TestCase;
use Illuminate\Http\Request;

it('ignores empty filter values', function () {
    $filters = new FilterCollection([
        TextFilter::make('name')->exact(),
    ]);

    $request = $this->request(function (Request $req) {
        $req->query->set('filters', ['name' => '']);
    });

    $mockedBuilder = mock(\Humweb\Table\Tests\Unit\TestBuilder::class)
        ->shouldReceive('getConnection')->andReturn(new \Humweb\Table\Tests\Unit\TestConnection(''))
        ->shouldReceive('where')->never()
        ->getMock();

    $filters->apply($request, $mockedBuilder);
});

it('applies default filter value when request omits field', function () {
    $filter = TextFilter::make('name')->exact();
    $filter->value = 'Alice';

    $filters = new FilterCollection([$filter]);

    $request = $this->request(function (Request $req) {
        $req->query->set('filters', ['other' => 'x']);
    });

    $mockedBuilder = mock(\Humweb\Table\Tests\Unit\TestBuilder::class)
        ->shouldReceive('where')->once()->andReturnSelf()
        ->shouldReceive('getConnection')->andReturn(new \Humweb\Table\Tests\Unit\TestConnection(''))
        ->getMock();

    $filters->apply($request, $mockedBuilder);
});


