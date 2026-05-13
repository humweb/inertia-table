<?php

declare(strict_types=1);

namespace Humweb\Table;

use Illuminate\Http\Request;

class TableRequest
{
    public function __construct(
        protected Request $request,
        protected string $key = 'default',
    ) {
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Get a query parameter, automatically prefixed with the table key.
     * The 'default' key uses unprefixed params for backward compatibility.
     */
    public function get(string $param, mixed $default = null): mixed
    {
        return $this->request->get($this->prefix($param), $default);
    }

    public function has(string $param): bool
    {
        return $this->request->has($this->prefix($param));
    }

    public function query(string $param, mixed $default = null): mixed
    {
        return $this->request->query($this->prefix($param), $default);
    }

    /**
     * Get a nested array parameter (e.g. 'filters', 'search') with key prefix support.
     *
     * @return array<string, mixed>
     */
    public function getArray(string $param): array
    {
        $value = $this->get($param, []);

        return is_array($value) ? $value : [];
    }

    public function getSortParam(): ?string
    {
        return $this->get('sort');
    }

    public function getPage(): int
    {
        return (int) $this->get('page', 1);
    }

    public function getPerPage(int $default = 15): int
    {
        return (int) $this->get('perPage', $default);
    }

    /**
     * @return array<string, mixed>
     */
    public function getSearchParams(): array
    {
        return $this->getArray('search');
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilterParams(): array
    {
        return $this->getArray('filters');
    }

    public function getHiddenColumns(): string
    {
        return (string) $this->query('hidden', '');
    }

    public function getPartialData(): ?string
    {
        return $this->request->headers->get('X-Inertia-Partial-Data');
    }

    protected function prefix(string $param): string
    {
        if ($this->key === 'default') {
            return $param;
        }

        return "{$this->key}.{$param}";
    }
}
