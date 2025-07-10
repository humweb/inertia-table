<?php

/** @noinspection ALL */

namespace Humweb\Table\Filters;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\Request;

class TrashedFilter extends SelectFilter
{
    public string $label = 'Trashed';

    public array $options = [
        'with' => 'With Disabled',
        'only' => 'Only Disabled',
    ];

    /**
     * @param  Request  $request
     * @param  Builder  $query
     * @param  string   $value
     *
     * @return $this|Filter|TrashedFilter|void
     */
    public function apply(Request $request, Builder $query, $value)
    {
        if ($value === 'with' && method_exists($query, 'withTrashed')) {
            $query->withTrashed();
        }

        if ($value === 'only' && method_exists($query, 'onlyTrashed')) {
            $query->onlyTrashed();
        }

        return $this;
    }
}
