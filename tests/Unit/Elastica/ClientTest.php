<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Client;

use Elastica\Client as BaseClient;
use Elastica\Connection;
use Elastica\JSON;
use Elastica\Request;
use Elastica\Response;
use Elastica\Transport\NullTransport;
use FOS\ElasticaBundle\Elastica\Client;
use FOS\ElasticaBundle\Logger\ElasticaLogger;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    private function getClientMock(Response $response = null)
    {
        $transport = new NullTransport();
        if ($response) {
            $transport->setResponse($response);
        }

        $connection = $this->createMock(Connection::class);
        $connection->expects($this->any())->method('getTransportObject')->will($this->returnValue($transport));
        $connection->expects($this->any())->method('toArray')->will($this->returnValue([]));

        $client = $this->getMockBuilder(Client::class)
            ->setMethods(['getConnection'])
            ->getMock();

        $client->expects($this->any())->method('getConnection')->will($this->returnValue($connection));
        return $client;
    }

    public function testRequestsAreLogged()
    {
        $client = $this->getClientMock();
        $logger = $this->createMock(ElasticaLogger::class);
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
        $client->setLogger($logger);

        $response = $client->request('foo');

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testRequestsWithTransportInfoErrorsRaiseExceptions()
    {
        $httpCode = 403;
        $responseString = JSON::stringify(['message' => 'some AWS error']);
        $transferInfo = [
            'request_header' => 'bar',
            'http_code' => $httpCode,
            'body' => $responseString,
        ];
        $response = new Response($responseString);
        $response->setTransferInfo($transferInfo);

        $client = $this->getClientMock($response);

        $desiredMessage = sprintf('Error in transportInfo: response code is %d, response body is %s', $httpCode, $responseString);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage($desiredMessage);
        $response = $client->request('foo');
    }
}
