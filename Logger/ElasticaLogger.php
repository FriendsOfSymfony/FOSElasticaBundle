<?php

namespace FOS\ElasticaBundle\Logger;

use Psr\Log\LoggerInterface;

/**
 * Logger for the Elastica.
 *
 * The {@link logQuery()} method is configured as the logger callable in the
 * service container.
 *
 * @author Gordon Franke <info@nevalon.de>
 */
class ElasticaLogger implements LoggerInterface
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
     * @param string $path       Path to call
     * @param string $method     Rest method to use (GET, POST, DELETE, PUT)
     * @param array  $data       Arguments
     * @param float  $time       Execution time
     * @param array  $connection Host, port, transport, and headers of the query
     * @param array  $query      Arguments
     */
    public function logQuery($path, $method, $data, $time, $connection = array(), $query = array(), $engineTime = 0, $itemCount = 0)
    {
        if ($this->debug) {
            $e = new \Exception();

            $this->queries[] = array(
                'path' => $path,
                'method' => $method,
                'data' => $data,
                'executionMS' => $time,
                'engineMS' => $engineTime,
                'connection' => $connection,
                'queryString' => $query,
                'itemCount' => $itemCount,
                'backtrace' => $e->getTraceAsString()
            );
        }

        if (null !== $this->logger) {
            $message = sprintf("%s (%s) %0.2f ms", $path, $method, $time * 1000);
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
    public function emergency($message, array $context = array())
    {
        return $this->logger->emergency($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function alert($message, array $context = array())
    {
        return $this->logger->alert($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function critical($message, array $context = array())
    {
        return $this->logger->critical($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function error($message, array $context = array())
    {
        return $this->logger->error($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function warning($message, array $context = array())
    {
        return $this->logger->warning($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function notice($message, array $context = array())
    {
        return $this->logger->notice($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function info($message, array $context = array())
    {
        return $this->logger->info($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function debug($message, array $context = array())
    {
        return $this->logger->debug($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = array())
    {
        return $this->logger->log($level, $message, $context);
    }
}
