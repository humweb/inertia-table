<?php

namespace Humweb\Table\Tests\Models;

use Humweb\Table\Fields\FieldCollection;
use Humweb\Table\Fields\ID;
use Humweb\Table\Fields\Text;
use Humweb\Table\Fields\Textarea;
use Humweb\Table\Filters\FilterCollection;
use Humweb\Table\Filters\TextFilter;
use Humweb\Table\Filters\TrashedFilter;
use Humweb\Table\Resource;
use Illuminate\Http\Request;

class UserResource extends Resource
{
    protected $model = User::class;

    public FieldCollection $field;
    public FilterCollection $filters;

    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    /**
     * @return FieldCollection
     */
    public function fields(): FieldCollection
    {
        return FieldCollection::make([
            ID::make('ID')->sortable()->searchable(),
            Text::make('Name')->sortable()->searchable(),
            Textarea::make('Email')->sortable(),
        ]);
    }

    /**
     * @return FilterCollection
     */
    public function filters(): FilterCollection
    {
        return FilterCollection::make([
            TextFilter::make('id')->exact()->rules('numeric'),
            TextFilter::make('name')->rules('string'),
            TextFilter::make('email')->fullSearch()->rules('string'),
            TrashedFilter::make('trashed'),
        ]);
    }

    public function globalFilter($query, $value)
    {
        return $query->where(function ($query) use ($value) {
            $query->when(is_numeric($value), function ($query, $bool) use ($value) {
                $query->orWhere('id', $value);
            })->when(! is_numeric($value), function ($query, $bool) use ($value) {
                $query->orWhere('name', 'ILIKE', "%{$value}%")
                    ->orWhere('email', 'ILIKE', "%{$value}%");
            });
        });
    }

    /**
     * @param  string  $model
     *
     * @return UserResource
     */
    public function model(string $model): UserResource
    {
        $this->model = $model;

        return $this;
    }

    public function filterSite($value)
    {
        return $value();
    }
}
