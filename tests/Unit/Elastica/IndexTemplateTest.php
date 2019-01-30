<?php

namespace FOS\ElasticaBundle\Tests\Unit\Elastica;

use Elastica\Client;
use FOS\ElasticaBundle\Elastica\IndexTemplate;
use Elastica\IndexTemplate as BaseIndexTemplate;
use PHPUnit\Framework\TestCase;

/**
 * @author Dmitry Balabka <dmitry.balabka@intexsys.lv>
 */
class IndexTemplateTest extends TestCase
{
    public function testInstantiate()
    {
        $template = new IndexTemplate($this->prophesize(Client::class)->reveal(), 'some_name');
        $this->assertInstanceOf(BaseIndexTemplate::class, $template);
    }
}
