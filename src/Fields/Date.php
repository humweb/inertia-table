<?php

namespace Humweb\Table\Fields;

use Carbon\Carbon;

class Date extends Field
{
    /**
     * @var string
     */
    public string $component = 'date-field';

    /**
     * @var string
     */
    public string $dateFormat = 'Y-m-d';

    /**
     * @param  string  $format
     * @return $this
     */
    public function dateFormat(string $format): static
    {
        $this->dateFormat = $format;

        return $this;
    }

    /**
     * @param $value
     * @return string
     */
    public function transform($value)
    {
        return Carbon::parse($value)->format($this->dateFormat);
    }
}
