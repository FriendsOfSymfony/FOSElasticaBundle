<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Elastica;

use Elastica\Connection;
use Elastica\Exception\ClientException;
use Elastica\JSON;
use Elastica\Request;
use Elastica\Response;
use Elastica\Transport\NullTransport;
use FOS\ElasticaBundle\Elastica\Client;
use FOS\ElasticaBundle\Event\ElasticaRequestExceptionEvent;
use FOS\ElasticaBundle\Event\PostElasticaRequestEvent;
use FOS\ElasticaBundle\Event\PreElasticaRequestEvent;
use FOS\ElasticaBundle\Logger\ElasticaLogger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
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
            )
        ;
        $client->setLogger($logger);

        $response = $client->request('foo');

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testSendsNormalEvents(): void
    {
        $client = $this->getClientMock();
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($invoke = $this->exactly(2))
            ->method('dispatch')
            ->with($this->callback(function ($o) use ($invoke): bool {
                $counter = $invoke->getInvocationCount() - 1;

                if ($counter > 1) {
                    return false;
                }

                if (0 === $counter) {
                    if (!($o instanceof PreElasticaRequestEvent)) {
                        return false;
                    }

                    $this->assertEquals('event', $o->getPath());
                    $this->assertEquals(Request::GET, $o->getMethod());
                    $this->assertEquals(['some' => 'data'], $o->getData());
                    $this->assertEquals(['query' => 'data'], $o->getQuery());
                    $this->assertEquals(Request::DEFAULT_CONTENT_TYPE, $o->getContentType());
                } elseif (1 === $counter) {
                    if (!($o instanceof PostElasticaRequestEvent)) {
                        return false;
                    }

                    $request = $o->getRequest();

                    $this->assertEquals('event', $request->getPath());
                    $this->assertEquals(Request::GET, $request->getMethod());
                    $this->assertEquals(['some' => 'data'], $request->getData());
                    $this->assertEquals(['query' => 'data'], $request->getQuery());
                    $this->assertEquals(Request::DEFAULT_CONTENT_TYPE, $request->getContentType());

                    $this->assertInstanceOf(Response::class, $o->getResponse());
                }

                return true;
            }));

        $client->setEventDispatcher($dispatcher);
        $client->request('event', Request::GET, ['some' => 'data'], ['query' => 'data']);
    }

    public function testSendsExceptionEvents(): void
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
        $connection->method('getTransportObject')
            ->willThrowException(new ClientException());

        $client = $this->getClientMock($response, $connection);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($invoke = $this->exactly(2))
            ->method('dispatch')
            ->with($this->callback(function ($o) use ($invoke): bool {
                $counter = $invoke->getInvocationCount() - 1;

                if ($counter > 1) {
                    return false;
                }

                if (0 === $counter) {
                    if (!($o instanceof PreElasticaRequestEvent)) {
                        return false;
                    }

                    $this->assertEquals('event', $o->getPath());
                    $this->assertEquals(Request::GET, $o->getMethod());
                    $this->assertEquals(['some' => 'data'], $o->getData());
                    $this->assertEquals(['query' => 'data'], $o->getQuery());
                    $this->assertEquals(Request::DEFAULT_CONTENT_TYPE, $o->getContentType());
                } elseif (1 === $counter) {
                    if (!($o instanceof ElasticaRequestExceptionEvent)) {
                        return false;
                    }

                    $request = $o->getRequest();

                    $this->assertEquals('event', $request->getPath());
                    $this->assertEquals(Request::GET, $request->getMethod());
                    $this->assertEquals(['some' => 'data'], $request->getData());
                    $this->assertEquals(['query' => 'data'], $request->getQuery());
                    $this->assertEquals(Request::DEFAULT_CONTENT_TYPE, $request->getContentType());

                    $this->assertInstanceOf(ClientException::class, $o->getException());
                }

                return true;
            }));

        $client->setEventDispatcher($dispatcher);
        $this->expectException(ClientException::class);
        $client->request('event', Request::GET, ['some' => 'data'], ['query' => 'data']);
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
            ->willReturn(true)
        ;
        $connection
            ->expects($this->exactly(1))
            ->method('getConfig')
            ->with('http_error_codes')
            ->willReturn([400, 403, 404])
        ;
        $client = $this->getClientMock($response, $connection);

        $desiredMessage = \sprintf('Error in transportInfo: response code is %d, response body is %s', $httpCode, $responseString);
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
            ->getMock()
        ;

        $client->expects($this->any())->method('getConnection')->will($this->returnValue($connection));

        return $client;
    }
}
