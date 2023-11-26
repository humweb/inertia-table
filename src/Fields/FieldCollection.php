<?php

namespace Humweb\Table\Fields;

use Humweb\Table\Contracts\FieldCollectionable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use JsonSerializable;

class FieldCollection extends Collection implements FieldCollectionable
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

    public function applyTransform($records)
    {
        $transformableFields = $this->filter(fn($field) => $field->hasTransform() || $field->hasCallableTransform());

        if ($transformableFields->isEmpty()) {
            return $records;
        }

        return $records->through(function ($record) use ($transformableFields) {
            $record = $record->toArray();

            foreach ($transformableFields as $field) {
                $value = Arr::get($record, $field->attribute);

                if ($field->hasTransform()) {
                    $value = $field->transform($value);
                } elseif ($field->hasCallableTransform()) {
                    $value = ($field->callableTransform)($value);
                }

                Arr::set($record, $field->attribute, $value);
            }

            return $record;
        });
    }

    /**
     * @return array|mixed[]
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
