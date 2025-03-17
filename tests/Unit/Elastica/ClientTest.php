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

use Elastic\Elasticsearch\Exception\ElasticsearchException;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Elastica\Exception\ClientException;
use Elastica\JSON;
use Elastica\Request;
use Elastica\Response;
use FOS\ElasticaBundle\Elastica\Client;
use FOS\ElasticaBundle\Event\ElasticaRequestExceptionEvent;
use FOS\ElasticaBundle\Event\PostElasticaRequestEvent;
use FOS\ElasticaBundle\Event\PreElasticaRequestEvent;
use FOS\ElasticaBundle\Logger\ElasticaLogger;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
class ClientTest extends TestCase
{
    public function testRequestsAreLogged()
    {
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

        $response = new \GuzzleHttp\Psr7\Response(
            200,
            ['Content-Type' => 'application/json', Elasticsearch::HEADER_CHECK => Elasticsearch::PRODUCT_NAME],
            \json_encode(['foo' => 'bar'], \JSON_THROW_ON_ERROR)
        );
        $client = $this->getClient($logger, $response);

        $response = $client->sendRequest(new \GuzzleHttp\Psr7\Request(
            Request::GET,
            'https://some.tld/foo'
        ));

        $this->assertInstanceOf(Elasticsearch::class, $response);
    }

    public function testSendsNormalEvents(): void
    {
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

                    $path = \ltrim($request->getUri()->getPath(), '/'); // to have the same result as in the 6.0
                    $method = $request->getMethod();
                    try {
                        $data = \json_decode((string) $request->getBody(), true, 512, \JSON_THROW_ON_ERROR);
                    } catch (\JsonException) {
                        $data = [];
                    }
                    $query = [];
                    \parse_str($request->getUri()->getQuery(), $query);

                    $this->assertEquals('event', $path);
                    $this->assertEquals(Request::GET, $method);
                    $this->assertEquals(['some' => 'data'], $data);
                    $this->assertEquals(['query' => 'data'], $query);
                    $this->assertEquals(Request::DEFAULT_CONTENT_TYPE, $request->getHeaderLine('Content-Type'));

                    $this->assertInstanceOf(Response::class, $o->getResponse());
                }

                return true;
            }))
        ;

        $response = new \GuzzleHttp\Psr7\Response(
            200,
            ['Content-Type' => 'application/json', Elasticsearch::HEADER_CHECK => Elasticsearch::PRODUCT_NAME],
            \json_encode(['foo' => 'bar'], \JSON_THROW_ON_ERROR)
        );
        $logger = $this->createMock(ElasticaLogger::class);
        $client = $this->getClient($logger, $response);
        $client->setEventDispatcher($dispatcher);

        $client->sendRequest(new \GuzzleHttp\Psr7\Request(
            Request::GET,
            'https://some.tld/event?'.\http_build_query(['query' => 'data']),
            ['Content-Type' => 'application/json'],
            \json_encode(['some' => 'data'], \JSON_THROW_ON_ERROR)
        ));
    }

    public function testSendsExceptionEvents(): void
    {
        $httpCode = 403;
        $responseString = JSON::stringify(['message' => 'some AWS error']);
        $response = new \GuzzleHttp\Psr7\Response(
            $httpCode,
            ['Content-Type' => 'application/json', Elasticsearch::HEADER_CHECK => Elasticsearch::PRODUCT_NAME],
            $responseString
        );

        $client = $this->getClient($this->createMock(LoggerInterface::class), $response);

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

                    $path = \ltrim($request->getUri()->getPath(), '/'); // to have the same result as in the 6.0
                    $method = $request->getMethod();
                    try {
                        $data = \json_decode((string) $request->getBody(), true, 512, \JSON_THROW_ON_ERROR);
                    } catch (\JsonException) {
                        $data = [];
                    }
                    $query = [];
                    \parse_str($request->getUri()->getQuery(), $query);

                    $this->assertEquals('event', $path);
                    $this->assertEquals(Request::GET, $method);
                    $this->assertEquals(['some' => 'data'], $data);
                    $this->assertEquals(['query' => 'data'], $query);
                    $this->assertEquals(Request::DEFAULT_CONTENT_TYPE, $request->getHeaderLine('Content-Type'));

                    $this->assertInstanceOf(ElasticsearchException::class, $o->getException());
                }

                return true;
            }))
        ;

        $client->setEventDispatcher($dispatcher);
        $this->expectException(ClientException::class);
        $client->sendRequest(new \GuzzleHttp\Psr7\Request(
            Request::GET,
            'https://some.tld/event?'.\http_build_query(['query' => 'data']),
            ['Content-Type' => 'application/json'],
            \json_encode(['some' => 'data'], \JSON_THROW_ON_ERROR)
        ));
    }

    public function testRequestsWithTransportInfoErrorsRaiseExceptions()
    {
        $httpCode = 403;
        $responseString = JSON::stringify(['message' => 'some AWS error']);
        $response = new \GuzzleHttp\Psr7\Response(
            $httpCode,
            ['Content-Type' => 'application/json', Elasticsearch::HEADER_CHECK => Elasticsearch::PRODUCT_NAME],
            $responseString
        );

        $client = $this->getClient($this->createMock(LoggerInterface::class), $response);

        $desiredMessage = \sprintf('Error in transportInfo: response code is %d, response body is %s', $httpCode, $responseString);
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage($desiredMessage);
        $client->sendRequest(new \GuzzleHttp\Psr7\Request(
            Request::GET,
            'https://some.tld/foo'
        ));
    }

    public function testGetIndexTemplate()
    {
        $client = new Client();
        $template = $client->getIndexTemplate('some_index');
        $this->assertSame($template, $client->getIndexTemplate('some_index'));
    }

    private function getClient(LoggerInterface $logger, \GuzzleHttp\Psr7\Response $response): Client
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient->expects($this->any())
            ->method('sendRequest')
            ->willReturn($response)
        ;

        return new Client(['transport_config' => ['http_client' => $httpClient]], [401, 402, 403], $logger);
    }
}
