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
    /**
     * @var ElasticaLogger
     */
    protected $logger;

    public function setLogger(ElasticaLogger $logger)
    {
        $this->logger = $logger;
    }

    public function request($path, $method = Request::GET, $data = array(), array $query = array())
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
