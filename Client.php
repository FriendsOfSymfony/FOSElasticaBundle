<?php

namespace FOS\ElasticaBundle;

use Elastica\Client as ElasticaClient;
use Elastica\Request;

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
            $this->_logger->logQuery($path, $method, $data, $time);
        }

        return $response;
    }
}
