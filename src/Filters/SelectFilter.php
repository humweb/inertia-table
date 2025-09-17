<?php

namespace Humweb\Table\Filters;

use Illuminate\Contracts\Database\Query\Builder;
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

    /**
     * @return SelectFilter
     */
    public function multiple(): SelectFilter
    {
        $this->multiple = true;

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
        $relation = $this->relation ?: (empty($this->whereHas) ? null : $this->field);
        if (! empty($relation)) {
            $query->whereHas($relation, function ($query) use ($value) {
                // If we don't specify a relation column we assume id or slug
                if (is_bool($this->relationColumn)) {
                    $field = is_numeric($value) ? 'id' : 'slug';
                } else {
                    $field = $this->relationColumn;
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

    public function jsonSerialize(): mixed
    {
        return array_merge(parent::jsonSerialize(), [
            'multiple' => $this->multiple,
        ]);
    }
}
