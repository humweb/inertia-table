<?php

namespace Humweb\Table\Filters;

use Humweb\Table\Validation\ValidatesCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

use JsonSerializable;

class FilterCollection extends Collection implements JsonSerializable
{
    use ValidatesCollection;

    public function apply(Request $request, $query)
    {
        $reqFilters = $request->get('filters');
        if ($reqFilters) {
            $this->validateFilterInput($reqFilters);
            $this->each(function (Filter $filter) use ($request, $query, $reqFilters) {
                if (isset($reqFilters[$filter->field])) {
                    $filter->value = $reqFilters[$filter->field];
                    $filter->apply($request, $query, $reqFilters[$filter->field]);
                }
            });
        }
    }

    /**
     * @return array|mixed[]|\TMapValue[]
     */
    public function toArray()
    {
        return $this->map(function ($value) {
            return $value instanceof JsonSerializable ? $value->jsonSerialize() : $value;
        })->all();
    }

    /**
     * @return array|mixed[]
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
