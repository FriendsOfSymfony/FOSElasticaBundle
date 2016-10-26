<?php

namespace FOS\ElasticaBundle\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

/**
 * Logger for the Elastica.
 *
 * The {@link logQuery()} method is configured as the logger callable in the
 * service container.
 *
 * @author Gordon Franke <info@nevalon.de>
 */
class ElasticaLogger extends AbstractLogger
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $queries = array();

    /**
     * @var boolean
     */
    protected $debug;

    /**
     * Constructor.
     *
     * @param LoggerInterface|null $logger The Symfony logger
     * @param boolean              $debug
     */
    public function __construct(LoggerInterface $logger = null, $debug = false)
    {
        $this->logger = $logger;
        $this->debug = $debug;
    }

    /**
     * Logs a query.
     *
     * @param string $path Path to call
     * @param string $method Rest method to use (GET, POST, DELETE, PUT)
     * @param array  $data Arguments
     * @param float  $queryTime Execution time (in seconds)
     * @param array  $connection Host, port, transport, and headers of the query
     * @param array  $query Arguments
     * @param int    $engineTime
     * @param int    $itemCount
     */
    public function logQuery($path, $method, $data, $queryTime, $connection = array(), $query = array(), $engineTime = 0, $itemCount = 0)
    {
        $executionMS = $queryTime * 1000;

        if ($this->debug) {
            $e = new \Exception();
            $this->queries[] = array(
                'path' => $path,
                'method' => $method,
                'data' => $data,
                'executionMS' => $executionMS,
                'engineMS' => $engineTime,
                'connection' => $connection,
                'queryString' => $query,
                'itemCount' => $itemCount,
                'backtrace' => $e->getTraceAsString()
            );
        }

        if (null !== $this->logger) {
            $message = sprintf("%s (%s) %0.2f ms", $path, $method, $executionMS);
            $this->logger->info($message, (array) $data);
        }
    }

    /**
     * Returns the number of queries that have been logged.
     *
     * @return integer The number of queries logged
     */
    public function getNbQueries()
    {
        return count($this->queries);
    }

    /**
     * Returns a human-readable array of queries logged.
     *
     * @return array An array of queries
     */
    public function getQueries()
    {
        return $this->queries;
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = array())
    {
        return $this->logger->log($level, $message, $context);
    }
}
