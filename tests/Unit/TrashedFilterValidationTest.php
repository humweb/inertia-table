<?php

use Humweb\Table\Filters\FilterCollection;
use Humweb\Table\Filters\TrashedFilter;
use Illuminate\Validation\ValidationException;

it('validates trashed filter values', function () {
    $filters = new FilterCollection([
        TrashedFilter::make('trashed'),
    ]);

    expect(fn () => $filters->validateFilterInput(['trashed' => 'bad']))
        ->toThrow(ValidationException::class);
});


