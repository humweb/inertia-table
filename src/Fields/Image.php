<?php

namespace Humweb\Table\Fields;

use Carbon\Carbon;

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
}
