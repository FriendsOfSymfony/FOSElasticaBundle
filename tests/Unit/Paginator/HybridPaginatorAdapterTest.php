<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Event;

use Elastica\Query;
use FOS\ElasticaBundle\Paginator\HybridPaginatorAdapter;
use FOS\ElasticaBundle\Tests\Unit\UnitTestHelper;

class HybridPaginatorAdapterTest extends UnitTestHelper
{
    public function testGetResults()
    {
        $searchable = $this->mockSearchable();
        $query = new Query();
        $transformer = $this->mockElasticaToModelTransformer();

        $adapter = $this->mockHybridPaginatorAdapter([$searchable, $query, $transformer]);
        $adapter->getResults(0, 0);
    }

    protected function mockHybridPaginatorAdapter($args)
    {
        $mock = $this
            ->getMockBuilder(HybridPaginatorAdapter::class)
            ->setConstructorArgs($args)
            ->setMethods(['getElasticaResults'])
            ->getMock();

        $resultSet = $this->mockResultSet();

        $mock
            ->expects($this->exactly(1))
            ->method('getElasticaResults')
            ->willReturn($resultSet);

        return $mock;
    }
}
