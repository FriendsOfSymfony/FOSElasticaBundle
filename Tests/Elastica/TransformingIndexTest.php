<?php

namespace FOS\ElasticaBundle\Tests\Client;

use FOS\ElasticaBundle\Elastica\TransformingIndex;

class TransformingIndexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \FOS\ElasticaBundle\Elastica\TransformingIndex
     */
    private $index;

    /**
     * @var \FOS\ElasticaBundle\Elastica\LoggingClient
     */
    private $client;

    public function testCreateSearch()
    {
        $search = $this->index->createSearch();

        $this->assertInstanceOf('FOS\ElasticaBundle\Elastica\TransformingSearch', $search);
    }

    public function testOverrideName()
    {
        $this->assertEquals('testindex', $this->index->getName());

        $this->index->overrideName('newindex');

        $this->assertEquals('newindex', $this->index->getName());
    }

    protected function setUp()
    {
        $this->client = $this->getMockBuilder('FOS\ElasticaBundle\Elastica\LoggingClient')
            ->disableOriginalConstructor()
            ->getMock();
        $this->index = new TransformingIndex($this->client, 'testindex');
    }
}
