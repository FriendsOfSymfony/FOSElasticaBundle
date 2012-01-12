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

    /**
     * Constructor
     **/
    public function __construct(ObjectPersisterInterface $objectPersister, $objectClass, array $events)
    {
        $this->objectPersister = $objectPersister;
        $this->objectClass     = $objectClass;
        $this->events          = $events;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return $this->events;
    }

}
