<?php

declare(strict_types=1);

use Humweb\Table\Fields\Actions;
use Humweb\Table\InertiaTable;
use Humweb\Table\TableRequest;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * What `resolveRouteUrls` forwards to the frontend for a row action.
 *
 * The keys are a whitelist, so anything an action config carries that is not named here is
 * silently dropped -- which is easy to mistake for a broken frontend. `method` already had a
 * matching bug in the consuming app: the renderer ignored it behind a dropdown and a DELETE
 * action went out as a GET.
 */
function tableWithActions(array $actions, array $row = ['id' => 7]): array
{
    Route::get('/things/{id}', fn () => null)->name('things.show');
    Route::delete('/things/{id}', fn () => null)->name('things.destroy');

    $table = new InertiaTable(new TableRequest(Request::createFromGlobals()));
    $table->columns->push(Actions::make()->actions($actions));
    $table->records = new LengthAwarePaginator([$row], 1, 15);

    return $table->resolve()['records'][0]['__actions']['actions'];
}

it('forwards a confirmation prompt for an action that cannot be undone', function () {
    $resolved = tableWithActions([[
        'label' => 'Delete',
        'route' => 'things.destroy',
        'params' => ['id'],
        'method' => 'delete',
        'confirm' => 'Delete this thing?',
    ]]);

    expect($resolved[0]['confirm'])->toBe('Delete this thing?')
        ->and($resolved[0]['method'])->toBe('delete')
        ->and($resolved[0]['url'])->toContain('/things/7');
});

/** Absent means "do not ask", and must not become the string "null" or an empty prompt. */
it('sends a null confirmation when the action does not ask', function () {
    $resolved = tableWithActions([[
        'label' => 'View',
        'route' => 'things.show',
        'params' => ['id'],
    ]]);

    expect($resolved[0])->toHaveKey('confirm')
        ->and($resolved[0]['confirm'])->toBeNull();
});

/** The existing keys keep their shape; this is an addition, not a rewrite. */
it('still forwards label, url, method and class', function () {
    $resolved = tableWithActions([[
        'label' => 'View',
        'route' => 'things.show',
        'params' => ['id'],
        'class' => 'bt bt-white',
    ]]);

    expect(array_keys($resolved[0]))->toBe(['label', 'url', 'method', 'class', 'confirm'])
        ->and($resolved[0]['label'])->toBe('View')
        ->and($resolved[0]['method'])->toBe('get')
        ->and($resolved[0]['class'])->toBe('bt bt-white');
});

it('forwards each action in a multi-action column independently', function () {
    $resolved = tableWithActions([
        ['label' => 'Edit', 'route' => 'things.show', 'params' => ['id']],
        ['label' => 'Delete', 'route' => 'things.destroy', 'params' => ['id'], 'method' => 'delete', 'confirm' => 'Sure?'],
    ]);

    expect($resolved)->toHaveCount(2)
        ->and($resolved[0]['confirm'])->toBeNull()
        ->and($resolved[1]['confirm'])->toBe('Sure?');
});
