<?php

namespace Humweb\Table\Fields;

use Carbon\Carbon;

class Image extends Field
{
    /**
     * @var string
     */
    public string $component = 'image-field';

    public string $class = '';

    public string $path = '';

    public function class($class = ''): static
    {
        $this->class = $class;

        return $this;
    }

    public function path(string $path): static
    {
        $this->path = rtrim($path, '/').'/';

        return $this;
    }

    public function transform($value)
    {
        $class = !empty($this->class) ? ' class="'.$this->class.'"' : '';
        return '<img src="'.$this->path.$value.'"'.$class.' />';
    }
}
