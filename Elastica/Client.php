<?php

namespace FOS\ElasticaBundle\Elastica;

use Elastica\Client as BaseClient;
use Elastica\Request;
use FOS\ElasticaBundle\Logger\ElasticaLogger;

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
     * {@inheritdoc}
     */
    public function request($path, $method = Request::GET, $data = array(), array $query = array())
    {
        $start = microtime(true);
        $response = parent::request($path, $method, $data, $query);

        if ($this->_logger and $this->_logger instanceof ElasticaLogger) {
            $time = microtime(true) - $start;

            $connection = $this->getLastRequest()->getConnection();

            $connection_array = array(
                'host'      => $connection->getHost(),
                'port'      => $connection->getPort(),
                'transport' => $connection->getTransport(),
                'headers'   => $connection->hasConfig('headers') ? $connection->getConfig('headers') : array(),
            );

            $this->_logger->logQuery($path, $method, $data, $time, $connection_array, $query);
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
}
