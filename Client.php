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

    public function setLogger(ElasticaLogger $logger)
    {
        $this->logger = $logger;
    }

    public function request($path, $method, $data = array())
    {
        $start = microtime(true);
        //this is ghetto, but i couldnt figure out another way of making our site continue to behave normally even if ES was not running.
        //Any improvements on this welcome. Perhaps another parameter that allows you to control if you want to ignore exceptions about ES not running
        try {
            $response = parent::request($path, $method, $data);
        } catch(\Exception $e) {
        	
            if (null !== $this->logger) {
                $this->logger->logError($e->getMessage());
            }        	
            //again, ghetto, but couldnt figure out how to return a default empty Elastica_Response
            return new \Elastica_Response('{"took":0,"timed_out":false,"hits":{"total":0,"max_score":0,"hits":[]}}');
        }

        if (null !== $this->logger) {
            $time = microtime(true) - $start;
            $this->logger->logQuery($path, $method, $data, $time);
        }

        return $response;
    }
}
