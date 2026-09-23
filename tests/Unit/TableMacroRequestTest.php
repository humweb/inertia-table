<?php

use Illuminate\Http\Request;
use Inertia\Inertia;

it('reads the current request, not the one bound at boot', function () {
    // The provider booted with the container's original request; bind a new one, as every
    // HTTP request (and every feature-test request) does.
    $current = Request::create('/users', 'GET', ['sort' => '-name', 'filters' => ['role' => 'admin']]);
    $current->headers->set('X-Inertia', 'true');
    app()->instance('request', $current);

    $captured = null;

    $response = Inertia::render('Users/Index')->table(function ($table) use (&$captured) {
        $captured = $table->getTableRequest();
    });

    // Props are lazy: resolving the response (as Inertia JSON, no root view) runs the table closure.
    $response->toResponse($current);

    expect($captured->getRequest())->toBe($current)
        ->and($captured->getSortParam())->toBe('-name')
        ->and($captured->getFilterParams())->toBe(['role' => 'admin']);
});
