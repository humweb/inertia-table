<?php

namespace Humweb\Table\Concerns;

trait HasClassAttribute
{
    public string $class = '';

    /**
     * @param  string  $class
     * @return $this
     */
    public function class(string $class = ''): static
    {
        $this->meta['class'] = $class;

        return $this;
    }
}
