<?php

namespace FOS\ElasticaBundle\Tests\Resetter;

use Elastica\Request;
use Elastica\Transport\Null as NullTransport;

class ClientTest extends \PHPUnit_Framework_TestCase
{
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
                $this->isType('array')
            );

        $client = $this->getMockBuilder('FOS\ElasticaBundle\Client')
            ->setMethods(array('getConnection'))
            ->getMock();

        $client->expects($this->any())->method('getConnection')->will($this->returnValue($connection));

        $client->setLogger($logger);

        $response = $client->request('foo');

        $this->assertInstanceOf('Elastica\Response', $response);
    }
}
