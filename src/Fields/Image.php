<?php

namespace Humweb\Table\Fields;

use Carbon\Carbon;
use Humweb\Table\Concerns\HasClassAttribute;

class Image extends Field
{
    use HasClassAttribute;

    /**
     * @var string
     */
    public string $component = 'image-field';

    /**
     * @param  string  $path
     * @return $this
     */
    public function path(string $path): static
    {
        $this->meta['path'] = rtrim($path, '/').'/';

        return $this;
    }
}
