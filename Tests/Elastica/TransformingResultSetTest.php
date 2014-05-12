<?php

namespace FOS\ElasticaBundle\Tests\Client;

use Elastica\Query;
use Elastica\Response;
use FOS\ElasticaBundle\Elastica\TransformingResult;
use FOS\ElasticaBundle\Elastica\TransformingResultSet;

class TransformingResultSetTest extends \PHPUnit_Framework_TestCase
{
    public function testTransformingResult()
    {
        $response = new Response(array('hits' => array(
            'hits' => array(
                array(),
                array(),
                array(),
            )
        )));
        $query = new Query();
        $transformer = $this->getMockBuilder('FOS\ElasticaBundle\Transformer\CombinedResultTransformer')
            ->disableOriginalConstructor()
            ->getMock();

        $resultSet = new TransformingResultSet($response, $query, $transformer);

        $this->assertCount(3, $resultSet);
        $this->assertInstanceOf('FOS\ElasticaBundle\Elastica\TransformingResult', $resultSet[0]);

        $transformer->expects($this->once())
            ->method('transform')
            ->with($resultSet->getResults());

        $resultSet->transform();
        $resultSet->transform();

        $this->assertSame(array(
            0 => null, 1 => null, 2 => null
        ), $resultSet->getTransformed());
    }
}
