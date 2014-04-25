<?php

namespace FOS\ElasticaBundle\Elastica;

use Elastica\Client as Client;
use Elastica\Request;
use FOS\ElasticaBundle\Logger\ElasticaLogger;
use FOS\ElasticaBundle\Transformer\CombinedResultTransformer;

/**
 * Extends the default Elastica client to provide logging for errors that occur
 * during communication with ElasticSearch.
 *
 * @author Gordon Franke <info@nevalon.de>
 */
class LoggingClient extends Client
{
    /**
     * @var CombinedResultTransformer
     */
    private $resultTransformer;

    public function __construct(array $config = array(), $callback = null, CombinedResultTransformer $resultTransformer)
    {
        parent::__construct($config, $callback);

        $this->resultTransformer = $resultTransformer;
    }

    /**
     * Overridden Elastica method to return TransformingIndex instances instead of the
     * default Index instances.
     *
     * @param string $name
     * @return TransformingIndex
     */
    public function getIndex($name)
    {
        return new TransformingIndex($this, $name, $this->resultTransformer);
    }

    /**
     * @return CombinedResultTransformer
     */
    public function getResultTransformer()
    {
        return $this->resultTransformer;
    }

    /**
     * {@inheritdoc}
     */
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
                'headers'   => $connection->getConfig('headers'),
            );

            $this->_logger->logQuery($path, $method, $data, $time, $connection_array, $query);
        }

        return $response;
    }
}
