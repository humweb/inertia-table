<?php

namespace Humweb\Table\Fields;

class Image extends Field
{
    /**
     * @var string
     */
    public string $component = 'image-field';

    public function class($class = ''): static
    {
        $this->meta['class'] = $class;

        return $this;
    }

    public function path(string $path): static
    {
        $this->meta['path'] = rtrim($path, '/').'/';

        return $this;
    }

    public function transform($value)
    {
        $class = ! empty($this->meta['class']) ? ' class="'.$this->meta['class'].'"' : '';

        return '<img src="'.$this->meta['path'].$value.'"'.$class.' />';
    }
}
