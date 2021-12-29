<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
    protected ?LoggerInterface $logger;
    protected array $queries = [];
    protected bool $debug;

    public function __construct(?LoggerInterface $logger = null, bool $debug = false)
    {
        $this->logger = $logger;
        $this->debug = $debug;
    }

    /**
     * Logs a query.
     *
     * @param string       $path       Path to call
     * @param string       $method     Rest method to use (GET, POST, DELETE, PUT)
     * @param array|string $data       Arguments
     * @param float        $queryTime  Execution time (in seconds)
     * @param array        $connection Host, port, transport, and headers of the query
     * @param array        $query      Arguments
     * @param int          $engineTime
     */
    public function logQuery(string $path, string $method, $data, $queryTime, $connection = [], $query = [], $engineTime = 0, int $itemCount = 0)
    {
        $executionMS = $queryTime * 1000;

        if ($this->debug) {
            $e = new \Exception();
            if (\is_string($data)) {
                $jsonStrings = \explode("\n", $data);
                $data = [];
                foreach ($jsonStrings as $json) {
                    if ('' != $json) {
                        $data[] = \json_decode($json, true);
                    }
                }
            } else {
                $data = [$data];
            }

            $this->queries[] = [
                'path' => $path,
                'method' => $method,
                'data' => $data,
                'executionMS' => $executionMS,
                'engineMS' => $engineTime,
                'connection' => $connection,
                'queryString' => $query,
                'itemCount' => $itemCount,
                'backtrace' => $e->getTraceAsString(),
            ];
        }

        if (null !== $this->logger) {
            $message = \sprintf('%s (%s) %0.2f ms', $path, $method, $executionMS);
            $this->logger->info($message, (array) $data);
        }
    }

    /**
     * Returns the number of queries that have been logged.
     */
    public function getNbQueries(): int
    {
        return \count($this->queries);
    }

    /**
     * Returns a human-readable array of queries logged.
     */
    public function getQueries(): array
    {
        return $this->queries;
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = []): void
    {
        $this->logger->log($level, $message, $context);
    }

    public function reset(): void
    {
        $this->queries = [];
    }
}
