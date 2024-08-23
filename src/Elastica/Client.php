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

use Elastic\Elasticsearch\Response\Elasticsearch;
use Elastica\Client as BaseClient;
use Elastica\Exception\ClientException;
use Elastica\Exception\ExceptionInterface;
use Elastica\Index as BaseIndex;
use Elastica\Request;
use Elastica\Response;
use FOS\ElasticaBundle\Logger\ElasticaLogger;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Extends the default Elastica client to provide logging for errors that occur
 * during communication with ElasticSearch.
 *
 * @author Gordon Franke <info@nevalon.de>
 */
class Client extends BaseClient
{
    /**
     * Stores created indexes to avoid recreation.
     *
     * @var array<string, BaseIndex>
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

    public function request(string $path, string $method = Request::GET, $data = [], string $contentType = Request::DEFAULT_CONTENT_TYPE): Response
    {
        $headers = [
            'Content-Type' => $contentType,
            'Accept' => $contentType,
        ];

        $request = $this->createRequest($method, $path, $headers, $data);
        $es = $this->sendRequest($request);

        return new Response($es->asString(), $es->getStatusCode());
    }

    public function sendRequest(RequestInterface $request): Elasticsearch
    {
        if ($this->stopwatch) {
            $this->stopwatch->start('es_request', 'fos_elastica');
        }

        $query = $request->getBody()->__toString();

        try {
            $es = parent::sendRequest($request);
            $response = new Response($es->asString(), $es->getStatusCode());
        } catch (ExceptionInterface $e) {
            $this->logQuery($request->getUri(), $request->getMethod(), $query, [], 0, 0, 0);
            throw $e;
        }

        $responseData = $response->getData();

        $statusCode = $response->getStatus();
        $connections = $this->_config->get('connections');
        $forbiddenHttpCodes = [];
        if (!empty($connections)) {
            $connection = $connections[0];
            $forbiddenHttpCodes = $connection['http_error_codes'] ?? [];
        }

        if (\in_array($statusCode, $forbiddenHttpCodes, true)) {
            $body = \json_encode($responseData);
            $message = \sprintf('Error in transportInfo: response code is %s, response body is %s', $statusCode, $body);
            throw new ClientException($message);
        }

        if (isset($responseData['took'], $responseData['hits'])) {
            $this->logQuery($request->getUri(), $request->getMethod(), $query, [], $responseData['took'], $response->getEngineTime(), $responseData['hits']['total']['value'] ?? 0);
        } else {
            $this->logQuery($request->getUri(), $request->getMethod(), $query, [], $responseData['took'] ?? 0, 0, 0);
        }

        if ($this->stopwatch) {
            $this->stopwatch->stop('es_request');
        }

        return $es;
    }

    public function getIndex(string $name): BaseIndex
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

    /**
     * Log the query if we have an instance of ElasticaLogger.
     *
     * @param array<mixed>|string $data
     * @param array<mixed>        $query
     * @param float               $queryTime
     * @param int                 $engineMS
     */
    private function logQuery(string $path, string $method, $data, array $query, $queryTime, $engineMS = 0, int $itemCount = 0): void
    {
        if (!$this->_logger instanceof ElasticaLogger) {
            return;
        }

        $connections = $this->_config->get('connections');

        $connection = $connections[0];

        $connectionArray = [
            'host' => $connection['host'] ?? null,
            'port' => $connection['port'] ?? null,
            'transport' => $connection['transport'] ?? null,
            'headers' => $connection['headers'] ?? [],
        ];

        $this->_logger->logQuery($path, $method, $data, $queryTime, $connectionArray, $query, $engineMS, $itemCount);
    }
}
