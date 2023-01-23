<?php

namespace Humweb\Table\Concerns;

trait Transformable
{
    public function hasTransform(): bool
    {
        return method_exists($this, 'transform');
    }
}
