<?php
namespace FOQ\ElasticaBundle;

use Elastica_Client;

/**
 * @author Gordon Franke <info@nevalon.de>
 */
class Client extends Elastica_Client
{
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    public function request($path, $method, $data = array()) {
        $this->logger->logQuery($path, $method, $data);

        return parent::request($path, $method, $data);
    }
}
