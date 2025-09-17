<?php

/** @noinspection ALL */

namespace Humweb\Table\Filters;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\Request;

class TrashedFilter extends SelectFilter
{
    public string $label = 'Trashed';

    public array $options = [
        'with' => 'With Trashed',
        'only' => 'Only Trashed',
    ];

    /**
     * @param  string             $field
     * @param  string             $label
     * @param  array              $options
     * @param  string|array|null  $value
     */
    public function __construct(string $field, string $label = '', array $options = [], string|array $value = null)
    {
        parent::__construct($field, $label, $options, $value);

        // Restrict accepted values to prevent invalid input
        $this->rules('in:with,only');
    }

    /**
     * @param  Request  $request
     * @param  Builder  $query
     * @param  string   $value
     *
     * @return $this|Filter|TrashedFilter|void
     */
    public function apply(Request $request, Builder $query, $value)
    {
        if ($value === 'with' && $query->hasMacro('withTrashed')) {
            $query->withTrashed();
        }

        if ($value === 'only' && $query->hasMacro('onlyTrashed')) {
            $query->onlyTrashed();
        }

        return $this;
    }
}
