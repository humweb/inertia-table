<?php

namespace Humweb\Table\Sorts;

enum SortType: string
{
    case String = 'string';
    case Integer = 'integer';
    case Date = 'date';
    case Auto = 'auto';
}
