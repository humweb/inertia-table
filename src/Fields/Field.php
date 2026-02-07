<?php

namespace Humweb\Table\Fields;

use Humweb\Table\Concerns\HasVisibility;
use Humweb\Table\Concerns\Makeable;
use Humweb\Table\Concerns\Metable;
use Humweb\Table\Concerns\Transformable;
use Humweb\Table\Sorts\BasicSort;
use Humweb\Table\Sorts\CollectionSort;
use Humweb\Table\Sorts\Sort;
use Humweb\Table\Sorts\SortMode;
use Humweb\Table\Sorts\SortType;
use Humweb\Table\Validation\HasValidationRules;
use Illuminate\Support\Str;
use JsonSerializable;

class Field implements JsonSerializable
{
    use Makeable;
    use Metable;
    use HasValidationRules;
    use Transformable;
    use HasVisibility;

    /**
     * @var string
     */
    public string $component;

    /**
     * The displayable name of the field.
     *
     * @var string|null
     */
    public ?string $name = null;

    /**
     * The attribute / column name of the field.
     *
     * @var string
     */
    public string $attribute;

    /**
     * The field's resolved value.
     *
     * @var mixed
     */
    public mixed $value = null;

    /**
     * @var mixed
     */
    public mixed $defaultValue = null;

    /**
     * Indicates if the field is nullable.
     *
     * @var bool
     */
    public bool $nullable = false;

    public bool $visibility = true;


    /**
     * @var bool|Sort
     */
    public bool|Sort $sortable = false;

    /**
     * @var Sort
     */
    public Sort $sortableStrategy;

    public ?CollectionSort $collectionSortStrategy = null;

    public SortMode $sortMode = SortMode::Query;

    public SortType $sortType = SortType::Auto;

    /**
     * @var bool
     */
    public bool $visible = true;

    /**
     * @var bool
     */
    public bool $searchable = false;

    public function __construct($name, $attribute = null)
    {
        if (is_null($this->name)) {
            $this->name = $name;
        }
        $this->attribute = $attribute ?? str_replace(' ', '_', Str::lower($name));
    }

    /**
     * Make the field sortable with an optional strategy and mode.
     *
     * @param  Sort|CollectionSort|null  $strategy
     * @param  SortMode                  $mode
     *
     * @return static
     */
    public function sortable(Sort|CollectionSort|null $strategy = null, SortMode $mode = SortMode::Query): static
    {
        $this->sortable = true;

        if ($strategy instanceof CollectionSort) {
            $this->sortMode = $mode === SortMode::Query ? SortMode::Collection : $mode;
            $this->collectionSortStrategy = $strategy;

            if ($strategy instanceof \Humweb\Table\Sorts\BasicCollectionSort) {
                $this->sortType = $strategy->type;
            }
        } else {
            $this->sortMode = $mode;
            $this->sortableStrategy = $strategy ?? new BasicSort();
        }

        return $this;
    }

    /**
     * Make the field sortable on the client (frontend) with no server round-trip.
     *
     * @param  SortType  $type
     *
     * @return static
     */
    public function sortableOnClient(SortType $type = SortType::Auto): static
    {
        $this->sortable = true;
        $this->sortMode = SortMode::Client;
        $this->sortType = $type;

        return $this;
    }

    /**
     *
     * @return Field
     */
    public function nullable(): Field
    {
        $this->nullable = true;

        return $this;
    }

    /**
     * Allows column visibility to be toggled
     *
     * @param  bool  $visibility
     *
     * @return Field
     */
    public function visibility(bool $visibility): Field
    {
        $this->visibility = $visibility;

        return $this;
    }

    /**
     *
     * @param  bool  $bool
     *
     * @return Field
     */
    public function visible(bool $bool): Field
    {
        $this->visible = $bool;

        return $this;
    }

    /**
     * @return Field
     */
    public function searchable(): Field
    {
        $this->searchable = true;

        return $this;
    }

    public function getRuleAttribute()
    {
        return $this->attribute;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return array_merge([
            'component' => $this->component,
            'attribute' => $this->attribute,
            'name' => $this->name,
            'nullable' => $this->nullable,
            'sortable' => (bool) $this->sortable,
            'sortMode' => $this->sortMode->value,
            'sortType' => $this->sortType->value,
            'visible' => $this->visible,
            'visibility' => $this->visibility,
            'searchable' => $this->searchable,
            'value' => is_null($this->value) ? $this->defaultValue : $this->value,
        ], $this->meta());
    }
}
