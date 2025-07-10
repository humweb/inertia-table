<?php

namespace Humweb\Table\Tests\Unit;

use Humweb\Table\Filters\Filter;
use Humweb\Table\Filters\FilterCollection;
use Humweb\Table\Tests\TestCase;
use Illuminate\Database\Connection;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Testing\Assert;

class FilterCollectionTest extends TestCase
{
    protected FilterCollection $fieldCollection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fieldCollection = FilterCollection::make([
            TestFilter::make('email')->exact(),
        ]);
    }

    public function test_it_can_apply_filters_to_collection()
    {
        $request = $this->request(function (Request $request) {
            $request->query->set('filters', ['email' => 'user@email.com']);
        });

        $mockedBuilder = mock(TestBuilder::class)
            ->shouldReceive('where')
            ->once()
            ->shouldReceive('getConnection')
            ->andReturn(new TestConnection(''))
            ->getMock();
        $this->fieldCollection->apply($request, $mockedBuilder);
    }

    public function test_it_can_return_serialized_collection()
    {
        Assert::assertArraySubset([
            [
                "component" => "text-filter",
                "field" => "email",
                "options" => [],
                "label" => "Email",
                "value" => null,
                "rules" => [],
            ],
        ], $this->fieldCollection->jsonSerialize());
    }
}


class TestFilter extends Filter
{
    public function apply(Request $request, Builder $query, $value)
    {
        $this->value = $value;
        $this->whereFilter($query, $value);

        return $this;
    }
}

class TestBuilder extends Builder
{
    public function getConnection()
    {
        return new TestConnection('');
    }

    public function withTrashed()
    {
    }

    public function onlyTrashed()
    {
    }
}

class TestConnection extends Connection
{
    public function getDriverName()
    {
        return config('database.default');
    }
}
