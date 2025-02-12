<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Elastica;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ElasticsearchException;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Elastica\Client as BaseClient;
use Elastica\Exception\ClientException;
use FOS\ElasticaBundle\Logger\ElasticaLogger;
use FOS\ElasticaBundle\Event\ElasticaRequestExceptionEvent;
use FOS\ElasticaBundle\Event\PostElasticaRequestEvent;
use FOS\ElasticaBundle\Event\PreElasticaRequestEvent;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Extends the default Elastica client to provide logging for errors that occur
 * during communication with ElasticSearch.
 *
 * @author Gordon Franke <info@nevalon.de>
 */
class Client extends BaseClient implements ResetInterface
{

    private array $forbiddenCodes;

    public function __construct(array|string $config = [], array $forbiddenCodes = [400, 403, 404], ?LoggerInterface $logger = null)
    {
        parent::__construct($config, $logger);

        $this->forbiddenCodes = $forbiddenCodes;
    }

    /**
     * Stores created indexes to avoid recreation.
     *
     * @var array<string, Index>
     */
    private $indexCache = [];

    /**
     * Stores created index template to avoid recreation.
     *
     * @var array<string, IndexTemplate>
     */
    private $indexTemplateCache = [];

    /**
     * Symfony's debugging Stopwatch.
     *
     * @var Stopwatch|null
     */
    private $stopwatch;

    private ?EventDispatcherInterface $dispatcher = null;

    public function sendRequest(RequestInterface $request): Elasticsearch
    {
        if ($this->stopwatch) {
            $this->stopwatch->start('es_request', 'fos_elastica');
        }

        $path = ltrim($request->getUri()->getPath(), '/'); // to have the same result as in the 6.0
        $method = $request->getMethod();
        try {
            $data = json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $data = [];
        }
        $query = [];
        parse_str($request->getUri()->getQuery(), $query);

        if ($this->dispatcher) {
            $this->dispatcher->dispatch(new PreElasticaRequestEvent($path, $method, $data, $query, $contentType));
        }

        $start = \microtime(true);
        try {
            $elasticResponse = parent::sendRequest($request);
            $response = $this->toElasticaResponse($elasticResponse);

            if ($this->dispatcher) {
                $this->dispatcher->dispatch(new PostElasticaRequestEvent($request, $response));
            }
        } catch (ClientResponseException $responseException) {
            $this->logQuery($path, $method, $data, $query, 0.0, 0, 0);

            $response = $responseException->getResponse();
            if (\in_array($response->getStatusCode(), $this->forbiddenCodes, true)) {
                $body = (string) $response->getBody();
                $message = \sprintf('Error in transportInfo: response code is %s, response body is %s', $response->getStatusCode(), $body);
                throw new ClientException($message);
            }

            if ($this->dispatcher) {
                $this->dispatcher->dispatch(new ElasticaRequestExceptionEvent($this->getLastRequest(), $e));
            }

            throw $responseException;
        } catch (ElasticsearchException $e) {
            $this->logQuery($path, $method, $data, $query, 0.0, 0, 0);

            if ($this->dispatcher) {
                $this->dispatcher->dispatch(new ElasticaRequestExceptionEvent($this->getLastRequest(), $e));
            }

            throw $e;
        }
        $end = \microtime(true);

        $responseData = $response->getData();

        if (isset($responseData['took'], $responseData['hits'])) {
            $this->logQuery($path, $method, $data, $query, $end - $start, $response->getEngineTime(), $responseData['hits']['total']['value'] ?? 0);
        } else {
            $this->logQuery($path, $method, $data, $query, $end - $start, 0, 0);
        }

        if ($this->stopwatch) {
            $this->stopwatch->stop('es_request');
        }

        return $elasticResponse;
    }

    public function getIndex(string $name): Index
    {
        // TODO PHP >= 7.4 ??=
        return $this->indexCache[$name] ?? ($this->indexCache[$name] = new Index($this, $name));
    }

    /**
     * @param string $name
     */
    public function getIndexTemplate($name): IndexTemplate
    {
        // TODO PHP >= 7.4 ??=
        return $this->indexTemplateCache[$name] ?? ($this->indexTemplateCache[$name] = new IndexTemplate($this, $name));
    }

    /**
     * Sets a stopwatch instance for debugging purposes.
     */
    public function setStopwatch(?Stopwatch $stopwatch = null): void
    {
        $this->stopwatch = $stopwatch;
    }

    public function setEventDispatcher(?EventDispatcherInterface $dispatcher = null): void
    {
        $this->dispatcher = $dispatcher;
    }

    public function reset(): void
    {
        $this->indexCache = [];
        $this->indexTemplateCache = [];
        $this->stopwatch = null;
    }

    /**
     * Log the query if we have an instance of ElasticaLogger.
     *
     * @param array<mixed>|string $data
     * @param array<mixed>        $query
     * @param float               $queryTime
     * @param int                 $engineMS
     */
    private function logQuery(string $path, string $method, $data, array $query, float $queryTime, $engineMS = 0, int $itemCount = 0): void
    {
        if (!$this->_logger instanceof ElasticaLogger) {
            return;
        }

        $uri = $this->getLastRequest()?->getUri();

        if (null === $uri) {
            return;
        }

        $connectionArray = [
            'host' => $uri->getHost(),
            'port' => $uri->getPort(),
            'transport' => $uri->getScheme(),
            'headers' => $this->getLastRequest()?->getHeaders() ?? [],
        ];

        $this->_logger->logQuery($path, $method, $data, $queryTime, $connectionArray, $query, $engineMS, $itemCount);
    }
}
