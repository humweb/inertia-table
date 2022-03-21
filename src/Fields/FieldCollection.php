<?php

namespace Humweb\Table\Fields;


use Illuminate\Support\Collection;
use JsonSerializable;

class FieldCollection extends Collection implements JsonSerializable
{

    public function find($attribute)
    {
        return $this->firstWhere('attribute', $attribute);
    }

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
