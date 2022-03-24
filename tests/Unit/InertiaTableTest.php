<?php

namespace Humweb\Table\Tests\Unit;

use Humweb\Table\Fields\Text;
use Humweb\Table\Filters\SelectFilter;
use Humweb\Table\InertiaTable;
use Illuminate\Http\Request;
use Illuminate\Testing\Assert;
use Orchestra\Testbench\TestCase;

class InertiaTableTest extends TestCase
{
    private function request(callable $callback = null): Request
    {
        $request = Request::createFromGlobals();

        return $callback ? tap($request, $callback) : $request;
    }

    /** @test */
    public function it_gets_the_sort_from_the_request_query()
    {
        $request = $this->request(function (Request $request) {
            $request->query->set('sort', 'name');
        });

        $props = (new InertiaTable($request))->buildTableProps();

        $this->assertEquals("name", $props['sort']);
    }

    /** @test */
    public function it_can_add_a_column_to_toggle()
    {
        $table = new InertiaTable($this->request());
        $table->columns->push(Text::make('name', 'Name'));

        $props = $table->buildTableProps();

        Assert::assertArraySubset([
            "columns" => [
                Text::make('name', 'Name'),
            ],
        ], $props);
    }

    /** @test */
    public function it_can_add_a_column_that_is_disabled_by_default()
    {
        $table = new InertiaTable($this->request());
        $table->columns->push(Text::make('name', 'Name')->visible(false));

        $props = $table->buildTableProps();

        Assert::assertArraySubset([
            "columns" => [
                Text::make('name', 'Name')->visible(false),
            ],
        ], $props);
    }

    /** @test */
    public function it_can_add_a_search_row()
    {
        $table = new InertiaTable($this->request());
        $table->columns([
            Text::make('Name')->searchable(),
        ]);

        $props = $table->buildTableProps();

        Assert::assertArraySubset([
            "attribute" => "name",
            "name" => "Name",
            "searchable" => true,
            "value" => null,
        ], $props['columns'][0]->jsonSerialize());
    }

    /** @test */
    public function it_gets_the_default_search_values_from_the_request_query()
    {
        $table = new InertiaTable($this->request(function (Request $request) {
            $request->query->set('search', [
                'name' => 'foobar',
            ]);
        }));

        $table->columns([
            Text::make('Name')->searchable(),
        ]);


        $props = $table->buildTableProps();

        $this->assertEquals('foobar', $props['search']['name']['value']);
    }

    /** @test */
    public function it_gets_the_default_filter_values_from_the_request_query()
    {
        $table = new InertiaTable($this->request(function (Request $request) {
            $request->query->set('filters', [
                'name' => 'foo',
                'email' => 'bar',
            ]);
        }));

        $table->filters([
            SelectFilter::make('name', 'Name', ['foo' => 'foobar']),
            SelectFilter::make('email', 'Email', ['bar' => 'bar@email.com', 'foo' => 'foo@email.com']),
        ]);

        $props = $table->buildTableProps();

        $this->assertEquals('foo', $props['filters'][0]->value);
        $this->assertEquals('bar', $props['filters'][1]->value);
    }

    /** @test */
    public function it_can_add_a_filter_with_options()
    {
        $table = new InertiaTable($this->request());
        $table->filters([
            SelectFilter::make('name', 'Name', [
            'a' => 'Option A',
            'b' => 'Option B',
        ]), ]);

        $props = $table->buildTableProps();

        Assert::assertArraySubset([
            "filters" => [
                SelectFilter::make('name', 'Name', [
                    'a' => 'Option A',
                    'b' => 'Option B',
                ]),
            ],
        ], $props);
    }
}
