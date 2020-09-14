<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Event;

use FOS\ElasticaBundle\Paginator\FantaPaginatorAdapter;
use FOS\ElasticaBundle\Paginator\PaginatorAdapterInterface;
use FOS\ElasticaBundle\Paginator\PartialResultsInterface;
use PHPUnit\Framework\TestCase;

class FantaPaginatorAdapterTest extends TestCase
{
    public function testGetNbResults()
    {
        $mock = $this->mockPaginatorAdapter();
        $mock
            ->expects($this->exactly(1))
            ->method('getTotalHits')
            ->willReturn(123);
        $adapter = new FantaPaginatorAdapter($mock);
        $this->assertEquals(123, $adapter->getNbResults());
    }

    public function testGetAggregations()
    {
        $mock = $this->mockPaginatorAdapter();
        $mock
            ->expects($this->exactly(1))
            ->method('getAggregations')
            ->willReturn([]);
        $adapter = new FantaPaginatorAdapter($mock);
        $this->assertEquals([], $adapter->getAggregations());
    }

    public function testGetSuggests()
    {
        $mock = $this->mockPaginatorAdapter();
        $mock
            ->expects($this->exactly(1))
            ->method('getSuggests')
            ->willReturn([]);
        $adapter = new FantaPaginatorAdapter($mock);
        $this->assertEquals([], $adapter->getSuggests());
    }

    public function testGetGetSlice()
    {
        $results = [];
        $resultsMock = $this->mockPartialResults($results);

        $mock = $this->mockPaginatorAdapter();
        $mock
            ->expects($this->exactly(1))
            ->method('getResults')
            ->with(1, 10)
            ->willReturn($resultsMock);
        $adapter = new FantaPaginatorAdapter($mock);
        $this->assertEquals($results, $adapter->getSlice(1, 10));
    }

    public function testGetMaxScore()
    {
        $mock = $this->mockPaginatorAdapter();
        $mock
            ->expects($this->exactly(1))
            ->method('getMaxScore')
            ->willReturn(123);
        $adapter = new FantaPaginatorAdapter($mock);
        $this->assertEquals(123, $adapter->getMaxScore());
    }

    private function mockPartialResults($results)
    {
        $mock = $this
            ->getMockBuilder(PartialResultsInterface::class)
            ->getMock();
        $mock
            ->expects($this->exactly(1))
            ->method('toArray')
            ->willReturn($results);

        return $mock;
    }

    private function mockPaginatorAdapter()
    {
        $mock = $this
            ->getMockBuilder(PaginatorAdapterInterface::class)
            ->getMock();

        return $mock;
    }
}
