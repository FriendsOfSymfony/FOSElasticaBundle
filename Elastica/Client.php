<?php

namespace FOS\ElasticaBundle\Elastica;

use Elastica\Client as BaseClient;
use Elastica\IndexTemplate;
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
    private $indexCache = array();

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
     * @param string $path
     * @param string $method
     * @param array  $data
     * @param array  $query
     *
     * @return \Elastica\Response
     */
    public function request($path, $method = Request::GET, $data = array(), array $query = array())
    {
        if ($this->stopwatch) {
            $this->stopwatch->start('es_request', 'fos_elastica');
        }

        $start = microtime(true);
        $response = parent::request($path, $method, $data, $query);
        $responseData = $response->getData();

        if (isset($responseData['took']) && isset($responseData['hits'])) {
            $this->logQuery($path, $method, $data, $query, $start, $response->getEngineTime(), $responseData['hits']['total']);
        } else {
            $this->logQuery($path, $method, $data, $query, $start, 0, 0);
        }

        if ($this->stopwatch) {
            $this->stopwatch->stop('es_request');
        }

        return $response;
    }

    public function getIndex($name)
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
     * @param array  $data
     * @param array  $query
     * @param int    $start
     */
    private function logQuery($path, $method, $data, array $query, $start, $engineMS = 0, $itemCount = 0)
    {
        if (!$this->_logger or !$this->_logger instanceof ElasticaLogger) {
            return;
        }

        $time = microtime(true) - $start;
        $connection = $this->getLastRequest()->getConnection();

        $connection_array = array(
            'host' => $connection->getHost(),
            'port' => $connection->getPort(),
            'transport' => $connection->getTransport(),
            'headers' => $connection->hasConfig('headers') ? $connection->getConfig('headers') : array(),
        );

        $this->_logger->logQuery($path, $method, $data, $time, $connection_array, $query, $engineMS, $itemCount);
    }
}
