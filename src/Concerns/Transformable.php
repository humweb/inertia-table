<?php

namespace Humweb\Table\Concerns;

trait Transformable
{

    /**
     * @var callable|null
     */
    public mixed $callableTransform = null;

    public function hasTransform(): bool
    {
        return method_exists($this, 'transform');
    }

    public function hasCallableTransform(): bool
    {
        return is_callable($this->callableTransform);
    }

    public function display(mixed $callableTransform): self
    {
        $this->callableTransform = $callableTransform;
        return $this;
    }
}
