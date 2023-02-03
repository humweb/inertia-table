<?php

namespace Humweb\Table\Filters;

use Humweb\Table\Concerns\Makeable;
use Humweb\Table\Concerns\Metable;
use Humweb\Table\Validation\HasValidationRules;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use JsonSerializable;

abstract class Filter implements JsonSerializable
{
    use Makeable;
    use Metable;
    use HasValidationRules;

    /**
     * @var string
     */
    public string $component = 'text-filter';

    /**
     * @var string
     */
    public string $field;

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

    public bool $startsWith = false;
    public bool $endsWith = false;
    public bool $fullSearch = false;
    public bool $exact = false;

    /**
     * @param  string             $field
     * @param  string             $label
     * @param  array              $options
     * @param  string|array|null  $value
     */
    public function __construct(string $field, string $label = '', array $options = [], string|array $value = null)
    {
        $this->field = $field;
        $this->value = $value;

        if (! empty($options)) {
            $this->options = $options;
        }

        if (! empty($label)) {
            $this->label = $label;
        }

        // Convert field to label if it's not set
        if (empty($this->label)) {
            $this->label = str_replace('_', ' ', Str::title($this->field));
        }
    }

    public function whereFilter($query, $value)
    {
        if (property_exists($this, 'multiple') && $this->multiple) {
            $query->whereIn($this->field, $value);

            return;
        }

        if ($this->exact) {
            $query->where($this->field, $value);

            return;
        }

        $field = $this->field;

        if ($query->getConnection()->getDriverName() == 'pgsql') {
            $like = 'ilike';
        } elseif ($query->getConnection()->getDriverName() == 'sqlite') {
            $like = 'like';
        } else {
            $field = "LOWER({$this->field})";
            $like = 'like';
        }


        if ($this->startsWith) {
            $query->where(DB::raw($field), $like, strtolower($value).'%');
        } elseif ($this->endsWith) {
            $query->where(DB::raw($field), $like, '%'.strtolower($value));
        } else {
            $query->where(DB::raw($field), $like, '%'.strtolower($value).'%');
        }
    }

    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Http\Request               $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed                                  $value
     *
     * @return Filter
     */
    abstract public function apply(Request $request, Builder $query, $value);

    /**
     * @param  string  $method
     * @param  mixed   $parameters
     *
     * @return Filter
     */
    public function __call($method, $parameters)
    {
        if (property_exists($this, $method)) {
            $this->$method = $parameters[0] ?? true;
        }

        return $this;
    }

    public function jsonSerialize(): mixed
    {
        return array_merge([
            'component' => $this->component,
            'field' => $this->field,
            'options' => $this->options,
            'label' => $this->label,
            'value' => $this->value,
            'rules' => $this->rules,
        ], $this->meta());
    }
}
