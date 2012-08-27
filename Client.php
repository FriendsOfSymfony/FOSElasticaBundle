<?php

namespace FOQ\ElasticaBundle;

use Elastica_Client;
use FOQ\ElasticaBundle\Logger\ElasticaLogger;

/**
 * @author Gordon Franke <info@nevalon.de>
 */
class Client extends Elastica_Client
{
    protected $logger;

    public function setLogger(ElasticaLogger $logger = null)
    {
        $this->logger = $logger;
    }

    public function request($path, $method, $data = array(), array $query = array())
    {
        $start = microtime(true);
        $response = parent::request($path, $method, $data, $query);

        if (null !== $this->logger) {
            $time = microtime(true) - $start;
            $this->logger->logQuery($path, $method, $data, $time);
        }

        return $response;
    }
}
