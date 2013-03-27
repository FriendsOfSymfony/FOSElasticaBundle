<?php

namespace FOS\ElasticaBundle;

use Elastica_Client;
use FOS\ElasticaBundle\Logger\ElasticaLogger;

/**
 * @author Gordon Franke <info@nevalon.de>
 */
class Client extends Elastica_Client
{
    protected $logger;

    public function setLogger(ElasticaLogger $logger)
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
