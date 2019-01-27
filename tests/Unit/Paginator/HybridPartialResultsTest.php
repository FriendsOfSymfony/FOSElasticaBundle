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

use FOS\ElasticaBundle\Paginator\HybridPartialResults;
use Elastica\ResultSet;
use FOS\ElasticaBundle\Tests\Unit\UnitTestHelper;

class HybridPartialResultsTest extends UnitTestHelper
{
    protected function mockResultSet()
    {
        $mock = $this
            ->getMockBuilder(ResultSet::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock
            ->expects($this->exactly(1))
            ->method('getResults')
            ->willReturn([]);

        return $mock;
    }

    public function testToArray()
    {
        $transformer = $this->mockElasticaToModelTransformer();

        $resultSet = $this->mockResultSet();

        $results = new HybridPartialResults($resultSet, $transformer);

        $results->toArray();
    }
}
