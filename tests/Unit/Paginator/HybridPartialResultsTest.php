<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Paginator;

use Elastica\ResultSet;
use FOS\ElasticaBundle\Paginator\HybridPartialResults;
use FOS\ElasticaBundle\Tests\Unit\UnitTestHelper;

/**
 * @internal
 */
class HybridPartialResultsTest extends UnitTestHelper
{
    public function testToArray()
    {
        $transformer = $this->mockElasticaToModelTransformer();
        $transformer
            ->expects($this->once())
            ->method('hybridTransform')
            ->willReturn([])
        ;

        $resultSet = $this->mockResultSet();

        $results = new HybridPartialResults($resultSet, $transformer);

        $results->toArray();
    }

    protected function mockResultSet()
    {
        $mock = $this
            ->getMockBuilder(ResultSet::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $mock
            ->expects($this->exactly(1))
            ->method('getResults')
            ->willReturn([])
        ;

        return $mock;
    }
}
