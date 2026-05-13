<?php

declare(strict_types=1);

use Humweb\Table\Fields\FieldCollection;
use Humweb\Table\Fields\Text;
use Humweb\Table\Pipeline\ApplyGlobalSearch;
use Humweb\Table\TableRequest;
use Humweb\Table\Tests\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

it('uses OR semantics for global search across multiple searchable fields', function () {
    DB::enableQueryLog();

    $httpRequest = Request::createFromGlobals();
    $httpRequest->query->set('search', ['global' => 'test']);

    $request = new TableRequest($httpRequest);
    $query = User::query();

    $fields = FieldCollection::make([
        Text::make('Name')->searchable(),
        Text::make('Email')->searchable(),
    ]);

    $stage = new ApplyGlobalSearch($fields);
    $stage->handle($query, $request, fn ($q) => $q);

    $sql = $query->toSql();
    expect($sql)->toContain('where (name like ? or email like ?)');
});

it('skips global search when value is empty', function () {
    $httpRequest = Request::createFromGlobals();
    $httpRequest->query->set('search', ['global' => '']);

    $request = new TableRequest($httpRequest);
    $query = User::query();

    $fields = FieldCollection::make([
        Text::make('Name')->searchable(),
    ]);

    $stage = new ApplyGlobalSearch($fields);
    $stage->handle($query, $request, fn ($q) => $q);

    $sql = $query->toSql();
    expect($sql)->not->toContain('where');
});

it('uses custom handler when provided', function () {
    $httpRequest = Request::createFromGlobals();
    $httpRequest->query->set('search', ['global' => 'custom']);

    $request = new TableRequest($httpRequest);
    $query = User::query();

    $fields = FieldCollection::make([
        Text::make('Name')->searchable(),
    ]);

    $called = false;
    $stage = new ApplyGlobalSearch($fields, function ($query, $value) use (&$called) {
        $called = true;
        $query->where('name', $value);
    });

    $stage->handle($query, $request, fn ($q) => $q);

    expect($called)->toBeTrue();
    expect($query->toSql())->toContain('where "name" = ?');
});
