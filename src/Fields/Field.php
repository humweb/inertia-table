<?php

namespace Humweb\Table\Fields;

use Humweb\Table\Concerns\Makeable;
use Humweb\Table\Concerns\Metable;
use Humweb\Table\Sorts\BasicSort;
use Humweb\Table\Sorts\Sort;
use Humweb\Table\Validation\HasValidationRules;
use Illuminate\Support\Str;
use JsonSerializable;

class Field implements JsonSerializable
{
    use Makeable;
    use Metable;
    use HasValidationRules;

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


    /**
     * @var bool|Sort
     */
    public bool|Sort $sortable = false;

    /**
     * @var Sort
     */
    public Sort $sortableStrategy;

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
     *
     * @param  Sort|null  $class
     *
     * @return Field
     */
    public function sortable(?Sort $class = null): Field
    {
        $this->sortable         = true;
        $this->sortableStrategy = is_null($class) ? new BasicSort() : $class;

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
            'component'  => $this->component,
            'attribute'  => $this->attribute,
            'name'       => $this->name,
            'nullable'   => $this->nullable,
            'sortable'   => $this->sortable,
            'visible'    => $this->visible,
            'searchable' => $this->searchable,
            'value'      => is_null($this->value) ? $this->defaultValue : $this->value,
        ], $this->meta());
    }
}
