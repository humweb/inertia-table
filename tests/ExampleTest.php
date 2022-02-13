<?php

use Humweb\InertiaTable\Fields\Field;

it('can set magic properties', function () {
    $field = Field::make('Name');

    expect($field->attribute)->toEqual('name');

    $field->attribute('users.name')->description('test');

    expect($field->attribute)->toEqual('users.name');
    expect($field->description)->toEqual('test');
});
