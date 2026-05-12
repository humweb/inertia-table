<?php

declare(strict_types=1);

namespace Humweb\Table\Concerns;

use Illuminate\Http\Request;

trait HasVisibility
{
    /** @var bool|callable */
    public $showOnIndex = true;

    /** @var bool|callable */
    public $showOnDetail = true;

    /** @var bool|callable */
    public $showOnCreate = true;

    /** @var bool|callable */
    public $showOnUpdate = true;

    public function shouldShowIn(string $context, Request $request, mixed $resource = null): bool
    {
        return match ($context) {
            'index' => $this->checkIfVisible('showOnIndex', $request, $resource),
            'detail' => $this->checkIfVisible('showOnDetail', $request, $resource),
            'create' => $this->checkIfVisible('showOnCreate', $request, $resource),
            'update' => $this->checkIfVisible('showOnUpdate', $request, $resource),
            default => false,
        };
    }

    public function showOnIndex(bool|callable $value = true): static
    {
        return $this->showHide('showOnIndex', $value);
    }

    public function showOnDetail(bool|callable $value = true): static
    {
        return $this->showHide('showOnDetail', $value);
    }

    public function showOnCreate(bool|callable $value = true): static
    {
        return $this->showHide('showOnCreate', $value);
    }

    public function showOnUpdate(bool|callable $value = true): static
    {
        return $this->showHide('showOnUpdate', $value);
    }

    public function checkIfVisible(string $property, Request $request, mixed $resource = null): bool
    {
        $value = $this->{$property} ?? null;

        if (is_bool($value)) {
            return $value;
        }

        if (is_callable($value)) {
            return $value($request, $resource);
        }

        return false;
    }

    private function showHide(string $property, bool|callable $value = true): static
    {
        $this->{$property} = $value;

        return $this;
    }
}
