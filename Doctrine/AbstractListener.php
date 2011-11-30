<?php

namespace FOQ\ElasticaBundle\Doctrine;

use FOQ\ElasticaBundle\Persister\ObjectPersisterInterface;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

abstract class AbstractListener
{
    /**
     * Object persister
     *
     * @var ObjectPersister
     */
    protected $objectPersister;

    /**
     * Class of the domain model
     *
     * @var string
     */
    protected $objectClass;

    /**
     * List of subscribed events
     *
     * @var array
     */
    protected $events;

    protected $logger;

    /**
     * Constructor
     **/
    public function __construct(ObjectPersisterInterface $objectPersister, $objectClass, array $events, LoggerInterface $logger = null)
    {
        $this->objectPersister = $objectPersister;
        $this->objectClass     = $objectClass;
        $this->events          = $events;
        $this->logger          = $logger;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return $this->events;
    }

    /**
     * Log the failure message if a logger is available
     *
     * $param string $message
     */
    protected function logFailure($message)
    {
        if (null !== $this->logger) {
            $this->logger->warn(sprintf('%s: %s', get_class($this), $message));
        }
    }
}
