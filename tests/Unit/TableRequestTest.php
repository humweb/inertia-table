<?php

declare(strict_types=1);

use Humweb\Table\TableRequest;
use Illuminate\Http\Request;

it('reads unprefixed params for default key', function () {
    $request = Request::createFromGlobals();
    $request->query->set('sort', 'name');
    $request->query->set('page', 2);
    $request->query->set('perPage', 25);

    $tableRequest = new TableRequest($request, 'default');

    expect($tableRequest->getSortParam())->toBe('name');
    expect($tableRequest->getPage())->toBe(2);
    expect($tableRequest->getPerPage())->toBe(25);
});

it('reads prefixed params for named key', function () {
    $request = Request::createFromGlobals();
    $request->query->set('users.sort', '-email');
    $request->query->set('users.page', 3);
    $request->query->set('users.perPage', 50);

    $tableRequest = new TableRequest($request, 'users');

    expect($tableRequest->getSortParam())->toBe('-email');
    expect($tableRequest->getPage())->toBe(3);
    expect($tableRequest->getPerPage())->toBe(50);
});

it('returns default values when params are missing', function () {
    $request = Request::createFromGlobals();
    $tableRequest = new TableRequest($request, 'default');

    expect($tableRequest->getSortParam())->toBeNull();
    expect($tableRequest->getPage())->toBe(1);
    expect($tableRequest->getPerPage())->toBe(15);
    expect($tableRequest->getSearchParams())->toBe([]);
    expect($tableRequest->getFilterParams())->toBe([]);
});

it('reads search and filter arrays', function () {
    $request = Request::createFromGlobals();
    $request->query->set('search', ['global' => 'test', 'name' => 'foo']);
    $request->query->set('filters', ['status' => 'active']);

    $tableRequest = new TableRequest($request, 'default');

    expect($tableRequest->getSearchParams())->toBe(['global' => 'test', 'name' => 'foo']);
    expect($tableRequest->getFilterParams())->toBe(['status' => 'active']);
});

it('reads hidden columns', function () {
    $request = Request::createFromGlobals();
    $request->query->set('hidden', 'email,phone');

    $tableRequest = new TableRequest($request, 'default');

    expect($tableRequest->getHiddenColumns())->toBe('email,phone');
});

it('has() checks prefixed params correctly', function () {
    $request = Request::createFromGlobals();
    $request->query->set('members.sort', 'name');

    $members = new TableRequest($request, 'members');
    $invites = new TableRequest($request, 'invites');

    expect($members->has('sort'))->toBeTrue();
    expect($invites->has('sort'))->toBeFalse();
});
