<?php

namespace Humweb\Table\Fields;

use Carbon\Carbon;

class Date extends Field
{
    /**
     * @var string
     */
    public string $component = 'date-field';

    public string $dateFormat = 'Y-m-d';


    public function dateFormat(string $format): static
    {
        $this->dateFormat = $format;

        return $this;
    }

    public function transform($value)
    {
        return Carbon::parse($value)->format($this->dateFormat);
    }
}
