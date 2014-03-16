<?php

namespace FOS\ElasticaBundle;

use Elastica\Client as ElasticaClient;
use Elastica\Request;
use FOS\ElasticaBundle\Logger\ElasticaLogger;

/**
 * @author Gordon Franke <info@nevalon.de>
 */
class Client extends ElasticaClient
{
    public function request($path, $method = Request::GET, $data = array(), array $query = array())
    {
        $start = microtime(true);
        $response = parent::request($path, $method, $data, $query);

        if (null !== $this->_logger and $this->_logger instanceof ElasticaLogger) {
            $time = microtime(true) - $start;

            $connection = $this->getLastRequest()->getConnection();

            $connection_array = array(
                'host'      => $connection->getHost(),
                'port'      => $connection->getPort(),
                'transport' => $connection->getTransport(),
            );

            $this->_logger->logQuery($path, $method, $data, $time, $connection_array, $query);
        }

        return $response;
    }

    public function getIndex($name)
    {
        return new DynamicIndex($this, $name);
    }
}
