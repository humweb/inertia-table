<?php

declare(strict_types=1);

use Humweb\Table\Filters\EmptyNotEmptyFilter;
use Humweb\Table\Tests\Models\User;
use Illuminate\Http\Request;

it('wraps empty filter conditions in a where group', function () {
    $filter = EmptyNotEmptyFilter::make('email');
    $query = User::query();
    $request = Request::createFromGlobals();

    $filter->apply($request, $query, 'empty');

    $sql = $query->toSql();
    expect($sql)->toContain('where ("email" is null or "email" = ?)');
});

it('wraps not_empty filter conditions in a where group', function () {
    $filter = EmptyNotEmptyFilter::make('email');
    $query = User::query();
    $request = Request::createFromGlobals();

    $filter->apply($request, $query, 'not_empty');

    $sql = $query->toSql();
    expect($sql)->toContain('where ("email" is not null and "email" != ?)');
});
