<?php

namespace Humweb\Table\Tests\Unit;

use Humweb\Table\Filters\SelectFilter;
use Humweb\Table\Tests\TestCase;
use Illuminate\Http\Request;

class SelectFilterTest extends TestCase
{
    public function test_it_can_build_validation_rules()
    {
        $filter = SelectFilter::make('email', 'Email', [1,2,3]);
        $this->assertEmpty($filter->whereHas);
        $filter->whereHas();
        $this->assertTrue($filter->whereHas);
    }

    public function test_it_can_apply_whereHas_filter()
    {
        $filter = SelectFilter::make('email', 'Email', [1,2,3])->whereHas();

        $request = $this->request(function (Request $request) {
            $request->query->set('filters', ['email' => 'user@email.com']);
        });

        $mockedBuilder = mock(TestBuilder::class)
            ->shouldReceive('where')
            ->with(['email', 'foo'])
            ->shouldReceive('whereHas')
            ->once()
            ->shouldReceive('getConnection')
            ->andReturn(new TestConnection(''))
            ->getMock();
        $filter->apply($request, $mockedBuilder, 'foo');
    }
}
