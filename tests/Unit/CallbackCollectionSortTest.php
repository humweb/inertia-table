<?php

use Humweb\Table\Sorts\CallbackCollectionSort;

it('sorts using a custom callback', function () {
    $collection = collect([
        ['score' => 85, 'name' => 'Bob'],
        ['score' => 92, 'name' => 'Alice'],
        ['score' => 78, 'name' => 'Charlie'],
    ]);

    $sort = new CallbackCollectionSort(function ($collection, $descending, $property) {
        return $descending
            ? $collection->sortByDesc($property)->values()
            : $collection->sortBy($property)->values();
    });

    $result = $sort($collection, false, 'score');
    expect($result->pluck('score')->all())->toBe([78, 85, 92]);
});

it('sorts descending using a custom callback', function () {
    $collection = collect([
        ['score' => 85, 'name' => 'Bob'],
        ['score' => 92, 'name' => 'Alice'],
        ['score' => 78, 'name' => 'Charlie'],
    ]);

    $sort = new CallbackCollectionSort(function ($collection, $descending, $property) {
        return $descending
            ? $collection->sortByDesc($property)->values()
            : $collection->sortBy($property)->values();
    });

    $result = $sort($collection, true, 'score');
    expect($result->pluck('score')->all())->toBe([92, 85, 78]);
});

it('passes all arguments to the callback', function () {
    $receivedArgs = [];

    $collection = collect([['val' => 1]]);

    $sort = new CallbackCollectionSort(function ($col, $desc, $prop) use (&$receivedArgs) {
        $receivedArgs = compact('col', 'desc', 'prop');

        return $col;
    });

    $sort($collection, true, 'val');

    expect($receivedArgs['desc'])->toBeTrue();
    expect($receivedArgs['prop'])->toBe('val');
    expect($receivedArgs['col'])->toBeInstanceOf(\Illuminate\Support\Collection::class);
});

it('supports complex multi-key sorting via callback', function () {
    $collection = collect([
        ['last' => 'Smith', 'first' => 'Zara'],
        ['last' => 'Smith', 'first' => 'Alice'],
        ['last' => 'Adams', 'first' => 'Bob'],
    ]);

    $sort = new CallbackCollectionSort(function ($collection, $descending) {
        $sorted = $collection->sortBy([
            ['last', $descending ? 'desc' : 'asc'],
            ['first', $descending ? 'desc' : 'asc'],
        ]);

        return $sorted->values();
    });

    $result = $sort($collection, false, 'last');
    expect($result->pluck('first')->all())->toBe(['Bob', 'Alice', 'Zara']);
});
