<?php

namespace FOS\ElasticaBundle\Tests\Client;

use FOS\ElasticaBundle\Elastica\TransformingResult;

class TransformingResultTest extends \PHPUnit_Framework_TestCase
{
    public function testTransformingResult()
    {
        $resultSet = $this->getMockBuilder('FOS\ElasticaBundle\Elastica\TransformingResultSet')
            ->disableOriginalConstructor()
            ->getMock();
        $result = new TransformingResult(array(), $resultSet);

        $resultSet->expects($this->exactly(2))
            ->method('transform');

        $result->getTransformed();
        $result->getTransformed();
    }
}
