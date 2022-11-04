<?php

namespace Humweb\Table\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SelectFilter extends Filter
{
    /**
     * @var string
     */
    public string $component = 'select-filter';

    /**
     * @var bool
     */
    public bool $multiple = false;

    public bool|string $whereHas = false;

    /**
     * @return SelectFilter
     */
    public function multiple(): SelectFilter
    {
        $this->multiple = true;

        return $this;
    }

    public function whereHas($column = true): SelectFilter
    {
        $this->whereHas = true;

        return $this;
    }

    /**
     * @param  Request       $request
     * @param  Builder       $query
     * @param  string|array  $value
     *
     * @return $this|Filter
     */
    public function apply(Request $request, Builder $query, $value)
    {
        if (! empty($this->whereHas)) {
            $query->whereHas($this->field, function ($query) use ($value) {
                // If we don't specify a field we assume the field is either id or slug
                if (is_bool($this->whereHas)) {
                    $field = is_numeric($value) ? 'id' : 'slug';
                } else {
                    $field = $this->whereHas;
                }
                $query->where($field, $value);
            });
        } else {
            if ($this->multiple) {
                $query->whereIn($this->field, $value);
            } else {
                $query->where($this->field, $value);
            }
        }


        return $this;
    }

    public function jsonSerialize()
    {
        return array_merge(parent::jsonSerialize(), [
            'multiple' => $this->multiple,
        ]);
    }
}
