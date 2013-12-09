<?php

namespace FOS\ElasticaBundle;

use Elastica\Client as ElasticaClient;
use Elastica\Request;
use Elastica\Transport\Http;
use Elastica\Transport\Https;

/**
 * @author Gordon Franke <info@nevalon.de>
 */
class Client extends ElasticaClient
{
    public function request($path, $method = Request::GET, $data = array(), array $query = array())
    {
        $start = microtime(true);
        $response = parent::request($path, $method, $data, $query);

        if (null !== $this->_logger) {
            $time = microtime(true) - $start;

            $connection = $this->getLastRequest()->getConnection();
            $transport  = $connection->getTransportObject();
            $full_host  = null;

            if ($transport instanceof Http || $transport instanceof Https) {
                $full_host = $connection->getTransport().'://'.$connection->getHost().':'.$connection->getPort();
            }

            $this->_logger->logQuery($path, $method, $data, $time, $full_host);
        }

        return $response;
    }
}
