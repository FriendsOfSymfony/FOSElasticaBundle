<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Elastica;

use Elastica\Client as BaseClient;
use Elastica\Exception\ClientException;
use Elastica\Index as BaseIndex;
use Elastica\Request;
use Elastica\Response;
use FOS\ElasticaBundle\Logger\ElasticaLogger;
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
     * @var array
     */
    private $indexCache = [];

    /**
     * Stores created index template to avoid recreation.
     *
     * @var array
     */
    private $indexTemplateCache = array();

    /**
     * Symfony's debugging Stopwatch.
     *
     * @var Stopwatch|null
     */
    private $stopwatch;

    /**
     * {@inheritdoc}
     */
    public function request($path, $method = Request::GET, $data = [], array $query = [], $contentType = Request::DEFAULT_CONTENT_TYPE): Response
    {
        if ($this->stopwatch) {
            $this->stopwatch->start('es_request', 'fos_elastica');
        }

        $response = parent::request($path, $method, $data, $query, $contentType);
        $responseData = $response->getData();

        $transportInfo = $response->getTransferInfo();
        $connection = $this->getLastRequest()->getConnection();
        $forbiddenHttpCodes = $connection->hasConfig('http_error_codes') ? $connection->getConfig('http_error_codes') : [];

        if (isset($transportInfo['http_code']) && in_array($transportInfo['http_code'], $forbiddenHttpCodes, true)) {
            $body = is_array($responseData) ? json_encode($responseData) : $responseData;
            $message = sprintf('Error in transportInfo: response code is %s, response body is %s', $transportInfo['http_code'], $body);
            throw new ClientException($message);
        }

        if (isset($responseData['took']) && isset($responseData['hits'])) {
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
        if (isset($this->indexCache[$name])) {
            return $this->indexCache[$name];
        }

        return $this->indexCache[$name] = new Index($this, $name);
    }

    public function getIndexTemplate($name)
    {
        if (isset($this->indexTemplateCache[$name])) {
            return $this->indexTemplateCache[$name];
        }

        return $this->indexTemplateCache[$name] = new IndexTemplate($this, $name);
    }

    /**
     * Sets a stopwatch instance for debugging purposes.
     *
     * @param Stopwatch $stopwatch
     */
    public function setStopwatch(Stopwatch $stopwatch = null)
    {
        $this->stopwatch = $stopwatch;
    }

    /**
     * Log the query if we have an instance of ElasticaLogger.
     *
     * @param string $path
     * @param string $method
     * @param array|string $data
     * @param array  $query
     * @param int    $queryTime
     * @param int    $engineMS
     * @param int    $itemCount
     */
    private function logQuery($path, $method, $data, array $query, $queryTime, $engineMS = 0, $itemCount = 0): void
    {
        if (!$this->_logger or !$this->_logger instanceof ElasticaLogger) {
            return;
        }

        $connection = $this->getLastRequest()->getConnection();

        $connectionArray = [
            'host' => $connection->getHost(),
            'port' => $connection->getPort(),
            'transport' => $connection->getTransport(),
            'headers' => $connection->hasConfig('headers') ? $connection->getConfig('headers') : [],
        ];

        /** @var ElasticaLogger $logger */
        $logger = $this->_logger;
        $logger->logQuery($path, $method, $data, $queryTime, $connectionArray, $query, $engineMS, $itemCount);
    }
}
