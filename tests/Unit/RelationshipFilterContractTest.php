<?php

use Humweb\Table\Filters\Filter;
use Humweb\Table\Filters\RelationshipFilter;

/**
 * RelationshipFilter used to redeclare things its parent already owned, in
 * shapes PHP rejects outright:
 *
 *   - `public string $relation` against Filter's `public ?string $relation`
 *   - `make(string $relation, string $column = 'id')` against the variadic
 *     `make(...$arguments)` that Filter inherits from the Makeable concern
 *
 * Both are fatal while *compiling* the class, so the whole filter was
 * unloadable — no call needed to trigger it. These tests pin the shapes.
 */
it('loads without a signature or property conflict against Filter', function () {
    expect(class_exists(RelationshipFilter::class))->toBeTrue();
    expect(is_subclass_of(RelationshipFilter::class, Filter::class))->toBeTrue();
});

it('keeps make() variadic so it stays compatible with Makeable', function () {
    $parent = new ReflectionMethod(Filter::class, 'make');
    $child = new ReflectionMethod(RelationshipFilter::class, 'make');

    expect($parent->isVariadic())->toBeTrue('Filter::make is expected to come from Makeable');
    expect($child->isVariadic())->toBeTrue('a narrower make() here is a fatal error, not a type error');
});

it('does not redeclare the parent relation property', function () {
    $declaring = (new ReflectionProperty(RelationshipFilter::class, 'relation'))->getDeclaringClass();

    expect($declaring->getName())->toBe(Filter::class);
});

it('still builds a relation filter through make()', function () {
    $filter = RelationshipFilter::make('roster', 'slug');

    expect($filter)->toBeInstanceOf(RelationshipFilter::class);
    expect($filter->relation)->toBe('roster');
    expect($filter->column)->toBe('slug');
});

it('defaults the column to id', function () {
    expect(RelationshipFilter::make('roster')->column)->toBe('id');
});
