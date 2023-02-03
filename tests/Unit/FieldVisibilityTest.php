<?php

use Humweb\Table\Fields\Text;

it('can hide from views', function ($context) {
    $field = Text::make('Title')->{'showOn'.$context}(false);
    expect($field->shouldShowIn(strtolower($context), $this->request(), []))->toBeFalse();

    $field = Text::make('Title')->{'showOn'.$context}(function () {
        return false;
    });
    expect($field->shouldShowIn(strtolower($context), $this->request(), []))->toBeFalse();
})->with([
    'Index',
    'Detail',
    'Create',
    'Update',
]);

it('can check visibility', function () {
    $field = Text::make('Title')->showOnIndex(false);
    expect($field->checkIfVisible('showOnIndex', $this->request(), []))->toBeFalse();

    $field = Text::make('Title')->showOnIndex(function () {
        return false;
    });
    expect($field->checkIfVisible('showOnIndex', $this->request(), []))->toBeFalse();
});

it('should return false for unknown context', function () {
    $field = Text::make('Title');
    expect($field->shouldShowIn('foo', $this->request(), []))->toBeFalse();
    expect($field->checkIfVisible('foo', $this->request(), []))->toBeFalse();
});
