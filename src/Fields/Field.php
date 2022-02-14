<?php

namespace Humweb\InertiaTable\Fields;

use Humweb\InertiaTable\Support\Makeable;

class Field
{
    use Makeable;

    /**
     * Field name user for title and headers
     *
     * @var string
     */
    public string $name;

    /**
     * Field data attribute/key
     *
     * @var string
     */
    public string $attribute;

    /**
     * Field type
     *
     * @var string
     */
    public string $type = 'text';

    /**
     *
     * @var string
     */
    public string $value;

    public bool $searchable = false;

    /**
     * Allow field to be sorted
     *
     * @var bool
     */
    public bool $sortable = false;


    /**
     * @var bool
     */
    public bool $hideable = true;
    public bool $enabled = true;


    /**
     * Filter for query
     *
     * @var callable
     */
    public $filter;


    /**
     * @param  string       $name
     * @param  string|null  $attribute
     * @param  string       $value
     */
    public function __construct(string $name, string $attribute = null, string $value = '')
    {
        $this->name = $name;
        $this->attribute = $attribute ?: strtolower($name);
        $this->value = $value;
    }

    /**
     * @return $this
     */
    public function searchable()
    {
        $this->searchable = true;

        return $this;
    }

    public function sortable()
    {
        $this->sortable = true;

        return $this;
    }

    public function hideable()
    {
        $this->hideable = true;

        return $this;
    }

    public function notHideable()
    {
        $this->hideable = false;

        return $this;
    }

    public function filter($filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * @param $method
     * @param $parameters
     *
     * @return void
     */
    public function __call($method, $parameters)
    {
        if (property_exists($this, $method)) {
            $this->$method = $parameters[0];

            return $this;
        }
    }
}
