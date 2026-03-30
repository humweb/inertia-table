<?php

use Humweb\Table\Fields\Text;
use Humweb\Table\Sorts\SortMode;
use Humweb\Table\Sorts\SortType;

it('serializes sortField as null by default', function () {
    $field = Text::make('Score', 'score')->sortable();

    $json = $field->jsonSerialize();

    expect($json['sortField'])->toBeNull();
});

it('serializes sortField when set', function () {
    $field = Text::make('Score', 'score')
        ->sortable(null, SortMode::Client)
        ->sortField('score_raw');

    $json = $field->jsonSerialize();

    expect($json['sortField'])->toBe('score_raw');
    expect($json['sortMode'])->toBe('client');
});

it('supports sortField with sortableOnClient', function () {
    $field = Text::make('HF', 'hf')
        ->sortableOnClient(SortType::Integer)
        ->sortField('hf_raw');

    $json = $field->jsonSerialize();

    expect($json['sortField'])->toBe('hf_raw');
    expect($json['sortMode'])->toBe('client');
    expect($json['sortType'])->toBe('integer');
    expect($json['attribute'])->toBe('hf');
});
