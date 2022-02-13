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
    public string $description;

    /**
     * Filter for query
     *
     * @var bool
     */
    public $filter;

    /**
     * Allow field to be sorted
     *
     * @var bool
     */
    public bool $sortable;

    /**
     * Decorate the value with a callback function
     *
     * @var callable
     */
    public $displayCallback;

    /**
     * Help text for forms and tooltips
     *
     * @var string
     */
    public string $helpText;

    public bool $hideable = true;


    /**
     * @param  string       $name
     * @param  string|null  $attribute
     * @param  string       $description
     */
    public function __construct(string $name, string $attribute = null, string $description = '')
    {
        $this->name        = $name;
        $this->attribute   = $attribute ?: strtolower($name);
        $this->description = $description;
    }

    public function sortable() {
        $this->sortable = true;
    }

    public function hideable() {
        $this->hideable = true;
    }

    public function filter($filter) {
        $this->filter = $filter;
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
