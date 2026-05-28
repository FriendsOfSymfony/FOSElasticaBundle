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
    public function testRequestsAreLogged(): void
    {
        $logger = $this->createMock(ElasticaLogger::class);
        $logger
            ->expects($this->once())
            ->method('logQuery')
            ->with(
                'foo',
                'GET',
                $this->isType('array'),
                $this->logicalOr(
                    $this->isType('float'),
                    $this->isNull()
                ),
                $this->isType('array'),
                $this->isType('array')
            )
        ;

        $response = new \Nyholm\Psr7\Response(
            200,
            ['Content-Type' => 'application/json', Elasticsearch::HEADER_CHECK => Elasticsearch::PRODUCT_NAME],
            \json_encode(['foo' => 'bar'], \JSON_THROW_ON_ERROR)
        );
        $client = $this->getClient($logger, $response);

        $response = $client->sendRequest(new \Nyholm\Psr7\Request(
            'GET',
            'https://some.tld/foo'
        ));

        $this->assertInstanceOf(Elasticsearch::class, $response);
    }

    public function testSendsNormalEvents(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($invoke = $this->exactly(2))
            ->method('dispatch')
            ->with($this->callback(function (object $o) use ($invoke): bool {
                $counter = $invoke->getInvocationCount() - 1;

                if ($counter > 1) {
                    return false;
                }

                if (0 === $counter) {
                    if (!($o instanceof PreElasticaRequestEvent)) {
                        return false;
                    }

                    $this->assertEquals('event', $o->getPath());
                    $this->assertEquals('GET', $o->getMethod());
                    $this->assertEquals(['some' => 'data'], $o->getData());
                    $this->assertEquals(['query' => 'data'], $o->getQuery());
                    $this->assertEquals(PreElasticaRequestEvent::DEFAULT_CONTENT_TYPE, $o->getContentType());
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
                    $this->assertEquals('GET', $method);
                    $this->assertEquals(['some' => 'data'], $data);
                    $this->assertEquals(['query' => 'data'], $query);
                    $this->assertEquals(PreElasticaRequestEvent::DEFAULT_CONTENT_TYPE, $request->getHeaderLine('Content-Type'));

                    $this->assertInstanceOf(Response::class, $o->getResponse());
                }

                return true;
            }))
        ;

        $response = new \Nyholm\Psr7\Response(
            200,
            ['Content-Type' => 'application/json', Elasticsearch::HEADER_CHECK => Elasticsearch::PRODUCT_NAME],
            \json_encode(['foo' => 'bar'], \JSON_THROW_ON_ERROR)
        );
        $logger = $this->createMock(ElasticaLogger::class);
        $client = $this->getClient($logger, $response);
        $client->setEventDispatcher($dispatcher);

        $client->sendRequest(new \Nyholm\Psr7\Request(
            'GET',
            'https://some.tld/event?'.\http_build_query(['query' => 'data']),
            ['Content-Type' => 'application/json'],
            \json_encode(['some' => 'data'], \JSON_THROW_ON_ERROR)
        ));
    }

    public function testSendsExceptionEvents(): void
    {
        $httpCode = 403;
        $responseString = JSON::stringify(['message' => 'some AWS error']);
        $response = new \Nyholm\Psr7\Response(
            $httpCode,
            ['Content-Type' => 'application/json', Elasticsearch::HEADER_CHECK => Elasticsearch::PRODUCT_NAME],
            $responseString
        );

        $client = $this->getClient($this->createMock(LoggerInterface::class), $response);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($invoke = $this->exactly(2))
            ->method('dispatch')
            ->with($this->callback(function (object $o) use ($invoke): bool {
                $counter = $invoke->getInvocationCount() - 1;

                if ($counter > 1) {
                    return false;
                }

                if (0 === $counter) {
                    if (!($o instanceof PreElasticaRequestEvent)) {
                        return false;
                    }

                    $this->assertEquals('event', $o->getPath());
                    $this->assertEquals('GET', $o->getMethod());
                    $this->assertEquals(['some' => 'data'], $o->getData());
                    $this->assertEquals(['query' => 'data'], $o->getQuery());
                    $this->assertEquals(PreElasticaRequestEvent::DEFAULT_CONTENT_TYPE, $o->getContentType());
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
                    $this->assertEquals('GET', $method);
                    $this->assertEquals(['some' => 'data'], $data);
                    $this->assertEquals(['query' => 'data'], $query);
                    $this->assertEquals(PreElasticaRequestEvent::DEFAULT_CONTENT_TYPE, $request->getHeaderLine('Content-Type'));

                    $this->assertInstanceOf(ElasticsearchException::class, $o->getException());
                }

                return true;
            }))
        ;

        $client->setEventDispatcher($dispatcher);
        $this->expectException(ClientException::class);
        $client->sendRequest(new \Nyholm\Psr7\Request(
            'GET',
            'https://some.tld/event?'.\http_build_query(['query' => 'data']),
            ['Content-Type' => 'application/json'],
            \json_encode(['some' => 'data'], \JSON_THROW_ON_ERROR)
        ));
    }

    public function testRequestsWithTransportInfoErrorsRaiseExceptions(): void
    {
        $httpCode = 403;
        $responseString = JSON::stringify(['message' => 'some AWS error']);
        $response = new \Nyholm\Psr7\Response(
            $httpCode,
            ['Content-Type' => 'application/json', Elasticsearch::HEADER_CHECK => Elasticsearch::PRODUCT_NAME],
            $responseString
        );

        $client = $this->getClient($this->createMock(LoggerInterface::class), $response);

        $desiredMessage = \sprintf('Error in transportInfo: response code is %d, response body is %s', $httpCode, $responseString);
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage($desiredMessage);
        $client->sendRequest(new \Nyholm\Psr7\Request(
            'GET',
            'https://some.tld/foo'
        ));
    }

    public function testGetIndexTemplate(): void
    {
        $client = new Client();
        $template = $client->getIndexTemplate('some_index');
        $this->assertSame($template, $client->getIndexTemplate('some_index'));
    }

    /**
     * Headers arriving via the deprecated http_client_options['headers'] passthrough are
     * extracted in the constructor and re-applied via Transport::setHeader. This makes
     * them work uniformly across Guzzle, Symfony HTTP Client, and elastic-transport's
     * bundled Curl client (which would otherwise ValueError on the string key in
     * curl_setopt_array).
     */
    public function testHeadersFromHttpClientOptionsAreReroutedViaTransport(): void
    {
        $client = new Client([
            'transport_config' => [
                'http_client_options' => [
                    'headers' => ['X-Foo' => 'bar', 'Authorization' => 'Bearer xyz'],
                    'timeout' => 5,
                ],
            ],
        ]);

        $headers = $client->getTransport()->getHeaders();
        self::assertSame('bar', $headers['X-Foo'] ?? null);
        self::assertSame('Bearer xyz', $headers['Authorization'] ?? null);
        // timeout is left in place so the underlying HTTP client (Guzzle/Symfony) can consume it
        self::assertSame(['timeout' => 5], $client->getConfig()['transport_config']['http_client_options']);
    }

    /**
     * When the active transport client is elastic-transport's bundled Curl, the deprecated
     * 'timeout' string key in http_client_options is translated to CURLOPT_TIMEOUT — otherwise
     * curl_setopt_array ValueErrors on the unknown option.
     */
    public function testTimeoutIsTranslatedToCurlOptForBundledCurl(): void
    {
        if (!\class_exists(\Elastic\Transport\Client\Curl::class)) {
            self::markTestSkipped('Bundled Elastic\Transport\Client\Curl is only available on Elastica 9+.');
        }

        $client = new Client([
            'transport_config' => [
                'http_client' => new \Elastic\Transport\Client\Curl(),
                'http_client_options' => [
                    'timeout' => 7,
                    \CURLOPT_RANDOM_FILE => '/dev/urandom',
                ],
            ],
        ]);

        $transportClient = $client->getTransport()->getClient();
        self::assertInstanceOf(\Elastic\Transport\Client\Curl::class, $transportClient);

        $optionsProperty = new \ReflectionProperty($transportClient, 'options');
        $options = $optionsProperty->getValue($transportClient);

        self::assertSame(7, $options[\CURLOPT_TIMEOUT] ?? null);
        self::assertArrayNotHasKey('timeout', $options);
        self::assertSame('/dev/urandom', $options[\CURLOPT_RANDOM_FILE] ?? null, 'other curl options must be preserved');
    }

    private function getClient(LoggerInterface $logger, \Nyholm\Psr7\Response $response): Client
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient->expects($this->any())
            ->method('sendRequest')
            ->willReturn($response)
        ;

        return new Client(['transport_config' => ['http_client' => $httpClient]], [401, 402, 403], $logger);
    }
}
