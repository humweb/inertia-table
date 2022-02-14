<?php

namespace Humweb\InertiaTable\Filters;

use Humweb\InertiaTable\Support\Makeable;

class Filter
{
    use Makeable;

    /**
     * @var string
     */
    public string $key;

    /**
     * @var array
     */
    public array $options = [];

    /**
     * @var string|array
     */
    public string|array|null $value;

    /**
     * @var string
     */
    public string $label;

    /**
     * @var string
     */
    public string $type;

    /**
     * @param  string             $key
     * @param  string             $label
     * @param  array              $options
     * @param  string|array|null  $value
     */
    public function __construct(string $key, string $label, array $options, string|array $value = null)
    {
        $this->key = $key;
        $this->options = $options;
        $this->label = $label;
        $this->value = $value;
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

    public function toArray()
    {
        return [
            'key' => $this->key,
            'options' => $this->options,
            'label' => $this->label,
            'value' => $this->value,
        ];
    }
}
