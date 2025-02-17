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

use Elastic\Elasticsearch\Response\Elasticsearch;
use Elastica\Exception\ClientException;
use Elastica\JSON;
use Elastica\Request;
use FOS\ElasticaBundle\Elastica\Client;
use FOS\ElasticaBundle\Logger\ElasticaLogger;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;

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
