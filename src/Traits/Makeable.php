<?php

namespace Humweb\Table\Traits;

trait Makeable
{
    /**
     * Create a new element.
     *
     * @return static
     */
    public static function make(...$arguments)
    {
        if (count($arguments)) {
            return new static(...$arguments);
        }

        return new static();
    }
}
