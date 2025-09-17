<?php

namespace Humweb\Table\Filters;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\Request;

class ScopeFilter extends Filter
{
    public string $component = 'scope-filter';

    public function apply(Request $request, Builder $query, $value)
    {
        if (empty($value) || ! is_string($value)) {
            return $this;
        }

        // Call local scope if available
        if (method_exists($query->getModel(), 'scope'.ucfirst($value))) {
            $query->{$value}();
        }

        return $this;
    }
}
