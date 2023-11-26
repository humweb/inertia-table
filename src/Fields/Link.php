<?php

namespace Humweb\Table\Fields;

use Humweb\Table\Concerns\HasClassAttribute;

class Link extends Field
{
    use HasClassAttribute;

    /**
     * @var string
     */
    public string $component = 'link-field';

    public function route($route, $params): static
    {
        $this->withMeta([
            'route' => $route,
            'routeParams' => $params,
        ]);

        return $this;
    }
}
