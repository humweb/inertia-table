<?php

namespace Humweb\Table\Filters;

use Humweb\Table\Contracts\FilterCollectionable;
use Humweb\Table\Validation\ValidatesCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use JsonSerializable;

class FilterCollection extends Collection implements FilterCollectionable
{
    use ValidatesCollection;

    /**
     * @param array $items
     *
     * @return FilterCollection
     */
    public static function make($items = []): FilterCollection
    {
        return new static($items);
    }

    public function apply(Request $request, $query)
    {
        $reqFilters = $request->get('filters');
        if ($reqFilters) {
            $this->validateFilterInput($reqFilters);
            $this->each(function (Filter $filter) use ($request, $query, $reqFilters) {
                $hasRequestValue = array_key_exists($filter->field, $reqFilters);
                $value = $hasRequestValue ? $reqFilters[$filter->field] : ($filter->value ?? null);

                // Ignore empty strings/null unless filter explicitly wants empties
                $shouldIgnore = $value === '' || $value === null;
                if ($shouldIgnore) {
                    return;
                }

                $filter->value = $value;
                $filter->apply($request, $query, $value);
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
