<?php

namespace FOQ\ElasticaBundle\Logger;

use Symfony\Component\HttpKernel\Log\LoggerInterface;

/**
 * Logger for the Elastica.
 *
 * The {@link logQuery()} method is configured as the logger callable in the
 * service container.
 *
 * @author Gordon Franke <info@nevalon.de>
 */
class ElasticaLogger
{
    protected $logger;
    protected $prefix;
    protected $queries;

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger The Symfony logger
     * @param string          $prefix A prefix for messages sent to the Symfony logger
     */
    public function __construct(LoggerInterface $logger = null, $prefix = 'Elastica')
    {
        $this->logger = $logger;
        $this->prefix = $prefix;
        $this->queries = array();
    }

    /**
     * Logs a query.
     *
     * This method is configured as the logger callable in the service
     * container.
     *
     * @param string $path Path to call
     * @param string $method Rest method to use (GET, POST, DELETE, PUT)
     * @param array  $data OPTIONAL Arguments as array
     */
    public function logQuery($path, $method, array $data = array())
    {
        $logInfo = sprintf("%s: %s (%s) \n%s", $this->prefix, $path, $method, json_encode($data));

        $this->queries[] = $logInfo;

        if (null !== $this->logger) {
            $this->logger->info($logInfo);
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
}
