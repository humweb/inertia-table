<?php

namespace Humweb\Table\Tests\Unit;

use Humweb\Table\Filters\TrashedFilter;
use Humweb\Table\Tests\TestCase;

class TrashedFilterTest extends TestCase
{
    public function test_it_can_apply_trashed_filter()
    {
        $filter = TrashedFilter::make('email', 'Email');

        $request = $this->request();

        $mockedBuilder = mock(TestBuilder::class)
            ->shouldReceive('withTrashed')
            ->once()
            ->shouldReceive('getConnection')
            ->andReturn(new TestConnection(''))
            ->getMock();

        $filter->apply($request, $mockedBuilder, 'with');

        $mockedBuilder = mock(TestBuilder::class)
            ->shouldReceive('onlyTrashed')
            ->once()
            ->shouldReceive('getConnection')
            ->andReturn(new TestConnection(''))
            ->getMock();

        $filter->apply($request, $mockedBuilder, 'only');
    }
}
