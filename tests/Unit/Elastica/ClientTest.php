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

use Elastica\Connection;
use Elastica\Exception\ClientException;
use Elastica\JSON;
use Elastica\Request;
use Elastica\Response;
use Elastica\Transport\NullTransport;
use FOS\ElasticaBundle\Elastica\Client;
use FOS\ElasticaBundle\Logger\ElasticaLogger;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
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

        $connection = $this->getConnectionMock();
        $connection
            ->expects($this->exactly(1))
            ->method('hasConfig')
            ->with('http_error_codes')
            ->willReturn(true);
        $connection
            ->expects($this->exactly(1))
            ->method('getConfig')
            ->with('http_error_codes')
            ->willReturn([400, 403, 404]);
        $client = $this->getClientMock($response, $connection);

        $desiredMessage = sprintf('Error in transportInfo: response code is %d, response body is %s', $httpCode, $responseString);
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage($desiredMessage);
        $response = $client->request('foo');
    }

    public function testGetIndexTemplate()
    {
        $client = new Client();
        $template = $client->getIndexTemplate('some_index');
        $this->assertSame($template, $client->getIndexTemplate('some_index'));
    }

    private function getConnectionMock()
    {
        $connection = $this->createMock(Connection::class);
        $connection->expects($this->any())->method('toArray')->will($this->returnValue([]));

        return $connection;
    }

    private function getClientMock(?Response $response = null, $connection = null)
    {
        $transport = new NullTransport();
        if ($response) {
            $transport->setResponse($response);
        }

        if (!$connection) {
            $connection = $this->getConnectionMock();
        }
        $connection->expects($this->any())->method('getTransportObject')->will($this->returnValue($transport));

        $client = $this->getMockBuilder(Client::class)
            ->setMethods(['getConnection'])
            ->getMock();

        $client->expects($this->any())->method('getConnection')->will($this->returnValue($connection));

        return $client;
    }
}
