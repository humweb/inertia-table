<?php

namespace Humweb\Table\Fields;

class Textarea extends Field
{

    /**
     * The field's component.
     *
     * @var string
     */
    public string $component = 'textarea-field';

    /**
     * The number of rows used for the textarea.
     *
     * @var int
     */
    public int $rows = 5;

    /**
     * Set the number of rows used for the textarea.
     *
     * @param  int $rows
     * @return $this
     */
    public function rows($rows): static
    {
        $this->rows = $rows;

        return $this;
    }


    /**
     * Prepare the element for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return array_merge(parent::jsonSerialize(), [
            'rows' => $this->rows
        ]);
    }
}
