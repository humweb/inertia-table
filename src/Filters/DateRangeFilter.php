<?php

namespace Humweb\Table\Filters;

use Carbon\Carbon;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\Request;

class DateRangeFilter extends Filter
{
    /**
     * @var string
     */
    public string $component = 'date-range-filter';

    public string $dateFormat = '';

    /**
     * @param  Request  $request
     * @param  Builder  $query
     * @param  string|array  $value
     *
     * @return $this|Filter
     */
    public function apply(Request $request, Builder $query, $value)
    {
        if (empty($this->dateFormat)) {
            return $this; // Guard: cannot parse without format
        }

        if (is_string($value)) {
            $value = explode('-', str_replace(' ', '', $value));
        }

        if (! is_array($value) || count($value) < 2 || empty($value[0]) || empty($value[1])) {
            return $this; // Ignore invalid/empty input
        }

        $start = Carbon::createFromFormat($this->dateFormat, $value[0]);
        $end = Carbon::createFromFormat($this->dateFormat, $value[1]);

        if ($start === false || $end === false) {
            return $this; // Invalid format
        }

        $this->value = [
            $start->startOfDay(),
            $end->endOfDay(),
        ];

        if (! empty($this->whereHas)) {
            $query->whereHas($this->whereHas, function ($query) {
                $query->whereBetween($this->field, $this->value);
            });
        } else {
            $query->whereBetween($this->field, $this->value);
        }

        return $this;
    }

    public function jsonSerialize(): mixed
    {
        return array_merge([
            'component' => $this->component,
            'field' => $this->field,
            'label' => $this->label,
            'value' => $this->value,
            'rules' => $this->rules,
            'dateFormat' => $this->dateFormat,
        ], $this->meta());
    }
}
