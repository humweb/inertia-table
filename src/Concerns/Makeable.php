<?php

namespace Humweb\Table\Concerns;

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
            /** @phpstan-ignore-next-line */
            return new static(...$arguments);
        }

        /** @phpstan-ignore-next-line */
        return new static();
    }
}
