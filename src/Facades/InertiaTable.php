<?php

namespace Humweb\InertiaTable\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Humweb\InertiaTable\InertiaTable
 */
class InertiaTable extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'inertia-table';
    }
}
