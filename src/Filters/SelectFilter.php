<?php

namespace Humweb\Table\Filters;

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

    public function apply(Request $request, $query, $value)
    {
        if ($this->multiple) {
            $query->whereIn($this->field, $value);
        }
        else {
            $query->where($this->field, $value);
        }
    }

    public function jsonSerialize()
    {
        return array_merge(parent::jsonSerialize(), [
            'multiple' => $this->multiple
        ]);
    }

}
