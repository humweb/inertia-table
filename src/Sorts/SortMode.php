<?php

namespace Humweb\Table\Sorts;

enum SortMode: string
{
    case Query = 'query';
    case Collection = 'collection';
    case Client = 'client';
}
