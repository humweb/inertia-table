<?php

namespace Humweb\Table\Filters;

use Illuminate\Http\Request;

class TrashedFilter extends SelectFilter
{
    public string $label = 'Trashed';

    public array $options = [
        'with' => 'With',
        'only' => 'Only',
    ];

    /** {@inheritdoc} */
    public function apply(Request $request, $query, $value)
    {
        if ($value === 'with') {
            $query->withTrashed();
        }

        if ($value === 'only') {
            $query->onlyTrashed();
        } else {
            $query->withoutTrashed();
        }
    }
}
