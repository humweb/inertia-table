<?php

use Humweb\Table\Fields\Computed;
use Humweb\Table\Fields\Date;
use Humweb\Table\Fields\Number;
use Humweb\Table\Fields\Text;
use Humweb\Table\Sorts\BasicCollectionSort;
use Humweb\Table\Sorts\BasicSort;
use Humweb\Table\Sorts\CallbackCollectionSort;
use Humweb\Table\Sorts\NullsLastSort;
use Humweb\Table\Sorts\SortMode;
use Humweb\Table\Sorts\SortType;

it('defaults to query sort mode', function () {
    $field = Text::make('Name')->sortable();

    expect($field->sortMode)->toBe(SortMode::Query);
    expect($field->sortableStrategy)->toBeInstanceOf(BasicSort::class);
});

it('sets query sort mode with custom strategy', function () {
    $field = Text::make('Name')->sortable(new NullsLastSort);

    expect($field->sortMode)->toBe(SortMode::Query);
    expect($field->sortableStrategy)->toBeInstanceOf(NullsLastSort::class);
});

it('auto-detects collection sort mode from CollectionSort strategy', function () {
    $field = Computed::make('Total')->sortable(new BasicCollectionSort);

    expect($field->sortMode)->toBe(SortMode::Collection);
    expect($field->collectionSortStrategy)->toBeInstanceOf(BasicCollectionSort::class);
});

it('inherits sort type from BasicCollectionSort', function () {
    $field = Number::make('Age')->sortable(new BasicCollectionSort(SortType::Integer));

    expect($field->sortType)->toBe(SortType::Integer);
    expect($field->sortMode)->toBe(SortMode::Collection);
});

it('inherits date sort type from BasicCollectionSort', function () {
    $field = Date::make('Created')->sortable(new BasicCollectionSort(SortType::Date));

    expect($field->sortType)->toBe(SortType::Date);
});

it('sets collection sort mode with CallbackCollectionSort', function () {
    $field = Computed::make('Score')->sortable(new CallbackCollectionSort(
        fn ($collection, $desc, $prop) => $collection->sortBy($prop)->values()
    ));

    expect($field->sortMode)->toBe(SortMode::Collection);
    expect($field->collectionSortStrategy)->toBeInstanceOf(CallbackCollectionSort::class);
});

it('sets client sort mode via sortableOnClient', function () {
    $field = Text::make('Name')->sortableOnClient();

    expect($field->sortable)->toBeTrue();
    expect($field->sortMode)->toBe(SortMode::Client);
    expect($field->sortType)->toBe(SortType::Auto);
});

it('sets client sort mode with explicit sort type', function () {
    $field = Number::make('Score')->sortableOnClient(SortType::Integer);

    expect($field->sortMode)->toBe(SortMode::Client);
    expect($field->sortType)->toBe(SortType::Integer);
});

it('sets client sort mode with date type', function () {
    $field = Date::make('Created At')->sortableOnClient(SortType::Date);

    expect($field->sortMode)->toBe(SortMode::Client);
    expect($field->sortType)->toBe(SortType::Date);
});

it('allows explicit sort mode override with collection sort', function () {
    $field = Text::make('Name')->sortable(new BasicCollectionSort, SortMode::Collection);

    expect($field->sortMode)->toBe(SortMode::Collection);
});

it('serializes sortMode and sortType in jsonSerialize for query sort', function () {
    $field = Text::make('Name')->sortable();
    $json = $field->jsonSerialize();

    expect($json['sortable'])->toBeTrue();
    expect($json['sortMode'])->toBe('query');
    expect($json['sortType'])->toBe('auto');
});

it('serializes sortMode and sortType for collection sort', function () {
    $field = Number::make('Age')->sortable(new BasicCollectionSort(SortType::Integer));
    $json = $field->jsonSerialize();

    expect($json['sortable'])->toBeTrue();
    expect($json['sortMode'])->toBe('collection');
    expect($json['sortType'])->toBe('integer');
});

it('serializes sortMode and sortType for client sort', function () {
    $field = Date::make('Created')->sortableOnClient(SortType::Date);
    $json = $field->jsonSerialize();

    expect($json['sortable'])->toBeTrue();
    expect($json['sortMode'])->toBe('client');
    expect($json['sortType'])->toBe('date');
});

it('serializes sortable as boolean false when not sortable', function () {
    $field = Text::make('Name');
    $json = $field->jsonSerialize();

    expect($json['sortable'])->toBeFalse();
    expect($json['sortMode'])->toBe('query');
    expect($json['sortType'])->toBe('auto');
});

it('includes all expected keys in serialized output', function () {
    $field = Text::make('Name')->sortableOnClient(SortType::String);
    $json = $field->jsonSerialize();

    expect($json)->toHaveKeys([
        'component',
        'attribute',
        'name',
        'nullable',
        'sortable',
        'sortMode',
        'sortType',
        'visible',
        'visibility',
        'searchable',
        'value',
    ]);
});
