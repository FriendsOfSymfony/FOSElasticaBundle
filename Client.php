<?php

namespace FOQ\ElasticaBundle;

use Elastica_Client;

/**
 * @author Gordon Franke <info@nevalon.de>
 */
class Client extends Elastica_Client
{
    protected $logger;

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    public function request($path, $method, $data = array())
    {
        $start = microtime(true);
        $response = parent::request($path, $method, $data);

        if (null !== $this->logger) {
            $time = microtime(true) - $start;
            $this->logger->logQuery($path, $method, $data, $time);
        }

        return $response;
    }
}
