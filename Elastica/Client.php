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
use Elastica\Request;
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
     * Symfony's debugging Stopwatch.
     *
     * @var Stopwatch|null
     */
    private $stopwatch;

    /**
     * @param string $path
     * @param string $method
     * @param array  $data
     * @param array  $query
     *
     * @return \Elastica\Response
     */
    public function request($path, $method = Request::GET, $data = [], array $query = [])
    {
        if ($this->stopwatch) {
            $this->stopwatch->start('es_request', 'fos_elastica');
        }

        $response = parent::request($path, $method, $data, $query);
        $responseData = $response->getData();

        if (isset($responseData['took']) && isset($responseData['hits'])) {
            $this->logQuery($path, $method, $data, $query, $response->getQueryTime(), $response->getEngineTime(), $responseData['hits']['total']);
        } else {
            $this->logQuery($path, $method, $data, $query, $response->getQueryTime(), 0, 0);
        }

        if ($this->stopwatch) {
            $this->stopwatch->stop('es_request');
        }

        return $response;
    }

    /**
     * @param string $name
     *
     * @return Index|mixed
     */
    public function getIndex($name)
    {
        if (isset($this->indexCache[$name])) {
            return $this->indexCache[$name];
        }

        return $this->indexCache[$name] = new Index($this, $name);
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
     * @param array  $data
     * @param array  $query
     * @param int    $queryTime
     * @param int    $engineMS
     * @param int    $itemCount
     */
    private function logQuery($path, $method, $data, array $query, $queryTime, $engineMS = 0, $itemCount = 0)
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
