<?php

namespace Humweb\Table\Filters;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
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
        if (is_string($value)) {
            $value = explode('-', str_replace(' ', '', $value));
        }

        $this->value = [
            Carbon::createFromFormat($this->dateFormat, $value[0])->startOfDay(),
            Carbon::createFromFormat($this->dateFormat, $value[1])->endOfDay(),
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
