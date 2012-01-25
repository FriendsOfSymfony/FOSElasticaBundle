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

    protected $identifier;
    protected $scheduledForRemoval;

    /**
     * Constructor
     **/
    public function __construct(ObjectPersisterInterface $objectPersister, $objectClass, array $events, $identifier = 'id')
    {
        $this->objectPersister     = $objectPersister;
        $this->objectClass         = $objectClass;
        $this->events              = $events;
        $this->identifier          = $identifier;
        $this->scheduledForRemoval = new \SplObjectStorage();
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return $this->events;
    }

    protected function scheduleForRemoval($object)
    {
        $getIdentifierMethod = 'get' . ucfirst($this->identifier);
        $this->scheduledForRemoval[$object] = $object->$getIdentifierMethod();
    }

    protected function removeIfScheduled($object)
    {
        if (isset($this->scheduledForRemoval[$object])) {
            $this->objectPersister->deleteById($this->scheduledForRemoval[$object]);
        }
    }

}
