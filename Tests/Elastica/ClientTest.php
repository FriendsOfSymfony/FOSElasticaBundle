<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Client;

use Elastica\Request;
use Elastica\Transport\NullTransport;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    public function testRequestsAreLogged()
    {
        $transport = new NullTransport();

        $connection = $this->getMockBuilder('Elastica\Connection')->getMock();
        $connection->expects($this->any())->method('getTransportObject')->will($this->returnValue($transport));
        $connection->expects($this->any())->method('toArray')->will($this->returnValue(array()));

        $logger = $this->getMockBuilder('FOS\ElasticaBundle\Logger\ElasticaLogger')->getMock();
        $logger
            ->expects($this->once())
            ->method('logQuery')
            ->with(
                'foo',
                Request::GET,
                $this->isType('array'),
                $this->logicalOr(
                    $this->isType('float'),
                    $this->isNull()
                ),
                $this->isType('array'),
                $this->isType('array')
            );

        $client = $this->getMockBuilder('FOS\ElasticaBundle\Elastica\Client')
            ->setMethods(array('getConnection'))
            ->getMock();

        $client->expects($this->any())->method('getConnection')->will($this->returnValue($connection));

        $client->setLogger($logger);

        $response = $client->request('foo');

        $this->assertInstanceOf('Elastica\Response', $response);
    }
}
