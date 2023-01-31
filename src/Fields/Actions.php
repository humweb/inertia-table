<?php

namespace Humweb\Table\Fields;

class Actions extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public string $component = 'action-field';

    public bool $visibility = true;

    /**
     * Create a new field.
     *
     * @param  string|null  $name
     * @param  string|null  $attribute
     * @return void
     */
    public function __construct($name = null, $attribute = null)
    {
        parent::__construct($name ?? 'Actions', $attribute);
    }

    public function actions(array $actions)
    {
        $this->meta['actions'] = $actions;

        return $this;
    }
}
