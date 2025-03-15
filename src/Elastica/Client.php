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

use Elastica\Client as BaseClient;
use Elastica\Exception\ClientException;
use Elastica\Exception\ExceptionInterface;
use Elastica\Index as BaseIndex;
use Elastica\Request;
use Elastica\Response;
use FOS\ElasticaBundle\Event\ElasticaRequestExceptionEvent;
use FOS\ElasticaBundle\Event\PostElasticaRequestEvent;
use FOS\ElasticaBundle\Event\PreElasticaRequestEvent;
use FOS\ElasticaBundle\Logger\ElasticaLogger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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

    private ?EventDispatcherInterface $dispatcher = null;

    /**
     * @param array<mixed> $data
     * @param array<mixed> $query
     */
    public function request(string $path, string $method = Request::GET, $data = [], array $query = [], string $contentType = Request::DEFAULT_CONTENT_TYPE): Response
    {
        if ($this->stopwatch) {
            $this->stopwatch->start('es_request', 'fos_elastica');
        }

        if ($this->dispatcher) {
            $this->dispatcher->dispatch(new PreElasticaRequestEvent($path, $method, $data, $query, $contentType));
        }

        try {
            $response = parent::request($path, $method, $data, $query, $contentType);

            if ($this->dispatcher) {
                $this->dispatcher->dispatch(new PostElasticaRequestEvent($this->getLastRequest(), $this->getLastResponse()));
            }
        } catch (ExceptionInterface $e) {
            $this->logQuery($path, $method, $data, $query, 0, 0, 0);

            if ($this->dispatcher) {
                $this->dispatcher->dispatch(new ElasticaRequestExceptionEvent($this->getLastRequest(), $e));
            }

            throw $e;
        }

        $responseData = $response->getData();

        $transportInfo = $response->getTransferInfo();
        $connection = $this->getLastRequest()->getConnection();
        $forbiddenHttpCodes = $connection->hasConfig('http_error_codes') ? $connection->getConfig('http_error_codes') : [];

        if (isset($transportInfo['http_code']) && \in_array($transportInfo['http_code'], $forbiddenHttpCodes, true)) {
            $body = \json_encode($responseData);
            $message = \sprintf('Error in transportInfo: response code is %s, response body is %s', $transportInfo['http_code'], $body);
            throw new ClientException($message);
        }

        if (isset($responseData['took'], $responseData['hits'])) {
            $this->logQuery($path, $method, $data, $query, $response->getQueryTime(), $response->getEngineTime(), $responseData['hits']['total']['value'] ?? 0);
        } else {
            $this->logQuery($path, $method, $data, $query, $response->getQueryTime(), 0, 0);
        }

        if ($this->stopwatch) {
            $this->stopwatch->stop('es_request');
        }

        return $response;
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

    public function setEventDispatcher(?EventDispatcherInterface $dispatcher = null): void
    {
        $this->dispatcher = $dispatcher;
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

        $connection = $this->getLastRequest()->getConnection();

        $connectionArray = [
            'host' => $connection->getHost(),
            'port' => $connection->getPort(),
            'transport' => $connection->getTransport(),
            'headers' => $connection->hasConfig('headers') ? $connection->getConfig('headers') : [],
        ];

        $this->_logger->logQuery($path, $method, $data, $queryTime, $connectionArray, $query, $engineMS, $itemCount);
    }
}
