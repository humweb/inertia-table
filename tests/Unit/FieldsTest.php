<?php

namespace Humweb\Table\Tests\Unit;

use Humweb\Table\Fields\FieldCollection;
use Humweb\Table\Fields\ID;
use Humweb\Table\Fields\Text;
use Humweb\Table\Fields\Textarea;

use Humweb\Table\Tests\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Testing\Assert;

beforeEach(function () {

    $this->fieldCollection = new FieldCollection([
        ID::make()->sortable(),
        Text::make('Title')->sortable()->display(fn($value) => '('.$value.')'),
        Textarea::make('Body')->sortable()->rows(10)->nullable(),
    ]);
});

test('can generate attribute', function () {
    $field = Text::make('Title');

    expect($field->attribute)->not->toBe('Title');
    expect($field->attribute)->toBe('title');

    expect(ID::make()->attribute)->toBe('id');
    expect(ID::make()->getRuleAttribute())->toBe('id');
});


test('can make field sortable and searchable', function () {
    $field = Text::make('Title')->sortable()->searchable();

    expect($field->sortable)->toBeTrue();
    expect($field->searchable)->toBeTrue();
});


test('can find field by attribute', function () {
    $id = $this->fieldCollection->find('id');
    $title = $this->fieldCollection->find('title');

    expect($id->attribute)->toBe('id');
    expect($title->attribute)->toBe('title');
});


test('can transform collection into array', function () {
    $collection = $this->fieldCollection->toArray();
    $serialize = $this->fieldCollection->jsonSerialize();

    expect($collection[0]['attribute'])->toBe('id');
    expect($serialize[0]['attribute'])->toBe('id');
});


test('can set textarea rows', function () {
    $body = $this->fieldCollection->find('body');

    expect($body->rows)->toBe(10);
});


test('can add meta data to field', function () {
    $field = Text::make('Title')->withMeta(['placeholder' => 'foobar']);

    expect($field->meta()['placeholder'])->toBe('foobar');
    expect($field->jsonSerialize())->toMatchArray([
        'placeholder' => 'foobar',
    ]);
});


test('can decorate value', function () {
    $transformed = $this->fieldCollection->applyTransform(new LengthAwarePaginator([new User(['title' => 'foobar'])], 1, 1));
    expect($transformed[0]['title'])->toBe('(foobar)');

});

