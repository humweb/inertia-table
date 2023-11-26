<?php

namespace Humweb\Table\Concerns;

trait HasClassAttribute
{
    public string $class = '';

    public function class($class = ''): static
    {
        $this->meta['class'] = $class;

        return $this;
    }
}
