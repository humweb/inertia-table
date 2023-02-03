<?php

namespace Humweb\Table\Concerns;

use Illuminate\Http\Request;

trait HasVisibility
{
    /**
     * @var bool|callable
     */
    public $showOnIndex = true;

    /**
     * @var bool|callable
     */
    public $showOnDetail = true;

    /**
     * @var bool|callable
     */
    public $showOnCreate = true;

    /**
     * @var bool|callable
     */
    public $showOnUpdate = true;


    /**
     * @param  string   $context
     * @param  Request  $request
     * @param           $resource
     *
     * @return bool
     */
    public function shouldShowIn(string $context, Request $request, $resource = null): bool
    {
        switch ($context) {
            case 'index':
                return $this->checkIfVisible('showOnIndex', $request, $resource);
            case 'detail':
                return $this->checkIfVisible('showOnDetail', $request, $resource);
            case 'create':
                return $this->checkIfVisible('showOnCreate', $request, $resource);
            case 'update':
                return $this->checkIfVisible('showOnUpdate', $request, $resource);
            default:
                return false;
        }
    }


    /**
     * @param  bool|callable  $value
     *
     * @return \Humweb\Illuminator\Fields\Field
     */
    public function showOnIndex(bool|callable $value = true)
    {
        return $this->showHide('showOnIndex', $value);
    }

    /**
     * @param  bool|callable  $value
     *
     * @return \Humweb\Illuminator\Fields\Field
     */
    public function showOnDetail(bool|callable $value = true)
    {
        return $this->showHide('showOnDetail', $value);
    }

    /**
     * @param  bool|callable  $value
     *
     * @return \Humweb\Illuminator\Fields\Field
     */
    public function showOnCreate(bool|callable $value = true)
    {
        return $this->showHide('showOnCreate', $value);
    }

    /**
     * @param  bool|callable  $value
     *
     * @return \Humweb\Illuminator\Fields\Field
     */
    public function showOnUpdate(bool|callable $value = true)
    {
        return $this->showHide('showOnUpdate', $value);
    }

    /**
     * @param  string   $property
     * @param  Request  $request
     * @param           $resource
     *
     * @return bool
     */
    public function checkIfVisible(string $property, Request $request, $resource = null): bool
    {
        $property = $this->{$property} ?? null;

        if (is_bool($property)) {
            return $property;
        } elseif (is_callable($property)) {
            return $property($request, $resource);
        }

        return false;
    }

    /**
     * @param  string         $property
     * @param  bool|callable  $value
     *
     * @return $this
     */
    private function showHide(string $property, bool|callable $value = true): static
    {
        $this->{$property} = $value;

        return $this;
    }

}
