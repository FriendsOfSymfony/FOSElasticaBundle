<?php

namespace FOS\ElasticaBundle\Tests\Client;

use Elastica\Request;
use Elastica\Transport\Null as NullTransport;
use FOS\ElasticaBundle\Elastica\LoggingClient;

class LoggingClientTest extends \PHPUnit_Framework_TestCase
{
    public function testOverriddenElasticaMethods()
    {
        $resultTransformer = $this->getMockBuilder('FOS\ElasticaBundle\Transformer\CombinedResultTransformer')
            ->disableOriginalConstructor()
            ->getMock();
        $client = new LoggingClient(array(), null, $resultTransformer);
        $index = $client->getIndex('index');
        $type = $index->getType('type');

        $this->assertInstanceOf('FOS\ElasticaBundle\Elastica\TransformingIndex', $index);
        $this->assertInstanceOf('FOS\ElasticaBundle\Elastica\TransformingType', $type);
    }

    public function testGetResultTransformer()
    {
        $resultTransformer = $this->getMockBuilder('FOS\ElasticaBundle\Transformer\CombinedResultTransformer')
            ->disableOriginalConstructor()
            ->getMock();
        $client = new LoggingClient(array(), null, $resultTransformer);

        $this->assertSame($resultTransformer, $client->getResultTransformer());
    }

    public function testRequestsAreLogged()
    {
        $transport = new NullTransport;

        $connection = $this->getMock('Elastica\Connection');
        $connection->expects($this->any())->method('getTransportObject')->will($this->returnValue($transport));
        $connection->expects($this->any())->method('toArray')->will($this->returnValue(array()));

        $logger = $this->getMock('FOS\ElasticaBundle\Logger\ElasticaLogger');
        $logger
            ->expects($this->once())
            ->method('logQuery')
            ->with(
                'foo',
                Request::GET,
                $this->isType('array'),
                $this->isType('float'),
                $this->isType('array'),
                $this->isType('array')
            );

        $client = $this->getMockBuilder('FOS\ElasticaBundle\Elastica\LoggingClient')
            ->disableOriginalConstructor()
            ->setMethods(array('getConnection'))
            ->getMock();

        $client->expects($this->any())->method('getConnection')->will($this->returnValue($connection));

        $client->setLogger($logger);

        $response = $client->request('foo');

        $this->assertInstanceOf('Elastica\Response', $response);
    }
}
