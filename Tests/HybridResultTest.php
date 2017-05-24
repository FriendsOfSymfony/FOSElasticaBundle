<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Resetter;

use Elastica\Result;
use FOS\ElasticaBundle\HybridResult;

class HybridResultTest extends \PHPUnit_Framework_TestCase
{
    public function testTransformedResultDefaultsToNull()
    {
        $result = new Result([]);

        $hybridResult = new HybridResult($result);

        $this->assertSame($result, $hybridResult->getResult());
        $this->assertNull($hybridResult->getTransformed());
    }
}
