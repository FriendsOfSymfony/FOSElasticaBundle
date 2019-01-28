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

use FOS\ElasticaBundle\Paginator\RawPaginatorAdapter;
use Elastica\Query;
use Elastica\ResultSet;
use Elastica\SearchableInterface;
use InvalidArgumentException;
use FOS\ElasticaBundle\Tests\Unit\UnitTestHelper;

class RawPaginatorAdapterTest extends UnitTestHelper
{
    protected function mockResultSet()
    {
        $methods = ['getTotalHits', 'getAggregations', 'getSuggests', 'getMaxScore'];
        $mock = $this
            ->getMockBuilder(ResultSet::class)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
        return $mock;
    }

    private function createAdapterWithSearch($methodName, $value)
    {
        $resultSet = $this->mockResultSet();
        $resultSet
            ->expects($this->exactly(1))
            ->method($methodName)
            ->willReturn($value);

        $query = new Query();
        $options = [];
        $searchable = $this->mockSearchable();
        $searchable
            ->expects($this->exactly(1))
            ->method('search')
            ->with($query)
            ->willReturn($resultSet);

        $adapter = new RawPaginatorAdapter($searchable, $query, $options);
        return $adapter;
    }

    private function createAdapterWithCount($totalHits, $querySize = null)
    {
        $query = new Query();
        if ($querySize) {
            $query->setParam('size', $querySize);
        }
        $options = [];
        $searchable = $this->mockSearchable();
        $searchable
            ->expects($this->exactly(1))
            ->method('count')
            ->willReturn($totalHits);

        $adapter = new RawPaginatorAdapter($searchable, $query, $options);
        return $adapter;
    }

    public function testGetTotalHits()
    {
        $adapter = $this->createAdapterWithCount(123);
        $this->assertEquals(123, $adapter->getTotalHits());

        $adapter = $this->createAdapterWithCount(123, 100);
        $this->assertEquals(100, $adapter->getTotalHits());
    }

    public function testGetTotalHitsGenuineTotal()
    {
        $adapter = $this->createAdapterWithCount(123);
        $this->assertEquals(123, $adapter->getTotalHits(true));

        $adapter = $this->createAdapterWithCount(123, 100);
        $this->assertEquals(123, $adapter->getTotalHits(true));
    }

    public function testGetAggregations()
    {
        $value = [];
        $adapter = $this->createAdapterWithSearch('getAggregations', $value);
        $this->assertEquals($value, $adapter->getAggregations());
    }

    public function testGetSuggests()
    {
        $value = [];
        $adapter = $this->createAdapterWithSearch('getSuggests', $value);
        $this->assertEquals($value, $adapter->getSuggests());
    }

    public function testGetMaxScore()
    {
        $value = 1.0;
        $adapter = $this->createAdapterWithSearch('getMaxScore', $value);
        $this->assertEquals($value, $adapter->getMaxScore());
    }

    public function testGetQuery()
    {
        $resultSet = $this->mockResultSet();

        $query = new Query();
        $options = [];
        $searchable = $this->mockSearchable($query);

        $adapter = new RawPaginatorAdapter($searchable, $query, $options);
        $this->assertEquals($query, $adapter->getQuery());
    }
}
