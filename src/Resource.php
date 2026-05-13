<?php

declare(strict_types=1);

namespace Humweb\Table;

use Humweb\Table\Concerns\ForwardsCalls;
use Humweb\Table\Concerns\HasResourceQueries;
use Humweb\Table\Concerns\Makeable;
use Humweb\Table\Contracts\FieldCollectionable;
use Humweb\Table\Contracts\FilterCollectionable;
use Humweb\Table\Fields\FieldCollection;
use Humweb\Table\Filters\FilterCollection;
use Humweb\Table\Sorts\Sort;
use Illuminate\Http\Request;

abstract class Resource
{
    use ForwardsCalls;
    use HasResourceQueries;
    use Makeable;

    public string $title;

    public string $primaryKey = 'id';

    /** @var array<string, mixed> */
    public array $parameters = [];

    protected Request $request;

    protected FilterCollection $filters;

    /** @var string|Sort */
    public string|Sort $defaultSort = 'id';

    public mixed $runtimeTransform = null;

    /** @var class-string<\Illuminate\Database\Eloquent\Model> */
    protected string $model;

    public function __construct(Request $request, array $parameters = [])
    {
        $this->newQuery();
        $this->request = $request;
        $this->parameters = $parameters;
    }

    /**
     * @param  string|array<string, string>  $key
     */
    public function addParameter(string|array $key, ?string $value = null): static
    {
        if (is_array($key)) {
            $this->parameters = array_merge($this->parameters, $key);
        } else {
            $this->parameters[$key] = $value;
        }

        return $this;
    }

    public function toResponse(InertiaTable $table): InertiaTable
    {
        $this->setTableRequest($table->getTableRequest());

        $tableRequest = $table->getTableRequest();
        $defaultPerPage = (int) config('inertia-table.pagination.default_per_page', 15);

        $table->columns($this->getFields())
            ->filters($this->getFilters())
            ->records($this->paginate($tableRequest->getPerPage($defaultPerPage)))
            ->globalSearch($this->hasGlobalFilter());

        return $table;
    }

    abstract public function fields(): FieldCollectionable;

    public function getFields(): FieldCollectionable
    {
        return $this->fields();
    }

    public function toForm(mixed $model = []): FieldCollection
    {
        $fields = $this->fields();
        $fields->fill($model);

        return $fields;
    }

    public function getFilters(): FilterCollectionable
    {
        return $this->filters()->filter(function ($filter) {
            return ! isset($this->parameters[$filter->field]);
        })->values();
    }

    public function filters(): FilterCollectionable
    {
        return new FilterCollection();
    }

    /**
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        return $this->forwardCallTo($this->query, $name, $arguments);
    }

    public function runtimeTransform(callable $runtimeTransform): static
    {
        $this->runtimeTransform = $runtimeTransform;

        return $this;
    }
}
