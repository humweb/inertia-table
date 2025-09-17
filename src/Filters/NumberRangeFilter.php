<?php

namespace Humweb\Table\Filters;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\Request;

class NumberRangeFilter extends Filter
{
    public string $component = 'number-range-filter';

    public function apply(Request $request, Builder $query, $value)
    {
        if (is_string($value)) {
            $value = explode(',', str_replace(' ', '', $value));
        }

        if (! is_array($value) || (count($value) < 2)) {
            return $this;
        }

        $min = is_numeric($value[0]) ? (float) $value[0] : null;
        $max = is_numeric($value[1]) ? (float) $value[1] : null;

        if ($min === null && $max === null) {
            return $this;
        }

        if (! empty($this->whereHas)) {
            $query->whereHas($this->whereHas, function ($q) use ($min, $max) {
                if ($min !== null) {
                    $q->where($this->field, '>=', $min);
                }
                if ($max !== null) {
                    $q->where($this->field, '<=', $max);
                }
            });
        } else {
            if ($min !== null) {
                $query->where($this->field, '>=', $min);
            }
            if ($max !== null) {
                $query->where($this->field, '<=', $max);
            }
        }

        return $this;
    }
}
