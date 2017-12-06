<?php

namespace FOS\ElasticaBundle\Tests;

use Elastica\Result;
use FOS\ElasticaBundle\HybridResult;

class HybridResultTest extends \PHPUnit_Framework_TestCase
{
    public function testTransformedResultDefaultsToNull()
    {
        $result = new Result(array());

        $hybridResult = new HybridResult($result);

        $this->assertSame($result, $hybridResult->getResult());
        $this->assertNull($hybridResult->getTransformed());
    }
}
