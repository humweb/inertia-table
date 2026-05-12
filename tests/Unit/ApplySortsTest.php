<?php

declare(strict_types=1);

use Humweb\Table\Fields\FieldCollection;
use Humweb\Table\Fields\Text;
use Humweb\Table\Pipeline\ApplySorts;
use Humweb\Table\Sorts\BasicSort;
use Humweb\Table\Sorts\CallbackSort;
use Humweb\Table\TableRequest;
use Humweb\Table\Tests\Models\User;
use Illuminate\Http\Request;

it('applies ascending sort via BasicSort', function () {
    $httpRequest = Request::createFromGlobals();
    $httpRequest->query->set('sort', 'name');

    $request = new TableRequest($httpRequest);
    $query = User::query();

    $fields = FieldCollection::make([
        Text::make('Name')->sortable(),
    ]);

    $stage = new ApplySorts($fields);
    $stage->handle($query, $request, fn ($q) => $q);

    expect($query->toSql())->toContain('order by "name" asc');
});

it('applies descending sort via BasicSort', function () {
    $httpRequest = Request::createFromGlobals();
    $httpRequest->query->set('sort', '-name');

    $request = new TableRequest($httpRequest);
    $query = User::query();

    $fields = FieldCollection::make([
        Text::make('Name')->sortable(),
    ]);

    $stage = new ApplySorts($fields);
    $stage->handle($query, $request, fn ($q) => $q);

    expect($query->toSql())->toContain('order by "name" desc');
});

it('uses sortField when defined on the field', function () {
    $httpRequest = Request::createFromGlobals();
    $httpRequest->query->set('sort', 'name');

    $request = new TableRequest($httpRequest);
    $query = User::query();

    $fields = FieldCollection::make([
        Text::make('Name')->sortable()->sortField('name_lower'),
    ]);

    $stage = new ApplySorts($fields);
    $stage->handle($query, $request, fn ($q) => $q);

    expect($query->toSql())->toContain('order by "name_lower" asc');
});

it('uses CallbackSort strategy', function () {
    $httpRequest = Request::createFromGlobals();
    $httpRequest->query->set('sort', 'name');

    $request = new TableRequest($httpRequest);
    $query = User::query();

    $fields = FieldCollection::make([
        Text::make('Name')->sortable(new CallbackSort(function ($query, $descending, $property) {
            $query->orderByRaw('LOWER("name") asc');
        })),
    ]);

    $stage = new ApplySorts($fields);
    $stage->handle($query, $request, fn ($q) => $q);

    expect($query->toSql())->toContain('LOWER("name") asc');
});

it('ignores sort for non-sortable fields', function () {
    $httpRequest = Request::createFromGlobals();
    $httpRequest->query->set('sort', 'name');

    $request = new TableRequest($httpRequest);
    $query = User::query();

    $fields = FieldCollection::make([
        Text::make('Name'),
    ]);

    $stage = new ApplySorts($fields);
    $stage->handle($query, $request, fn ($q) => $q);

    expect($query->toSql())->not->toContain('order by');
});
