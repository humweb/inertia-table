<?php

namespace Humweb\Table\Concerns;

trait ForwardsCalls
{
    /**
     * @param  object  $object
     * @param  string  $method
     * @param  array   $parameters
     *
     * @codeCoverageIgnore
     * @return mixed
     */
    protected function forwardCallTo(object $object, string $method, array $parameters): mixed
    {
        return $object->{$method}(...$parameters);
    }
}
