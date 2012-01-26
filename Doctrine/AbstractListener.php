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

    protected $esIdentifierField;
    protected $scheduledForRemoval;

    /**
     * Constructor
     **/
    public function __construct(ObjectPersisterInterface $objectPersister, $objectClass, array $events, $esIdentifierField = 'id')
    {
        $this->objectPersister     = $objectPersister;
        $this->objectClass         = $objectClass;
        $this->events              = $events;
        $this->esIdentifierField   = $esIdentifierField;
        $this->scheduledForRemoval = array();
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
        $getEsIdentifierMethod = 'get' . ucfirst($this->esIdentifierField);
        $this->scheduledForRemoval[spl_object_hash($object)] = $object->$getEsIdentifierMethod();
    }

    protected function removeIfScheduled($object)
    {
        $objectHash = spl_object_hash($object);
        if (isset($this->scheduledForRemoval[$objectHash])) {
            $this->objectPersister->deleteById($this->scheduledForRemoval[$objectHash]);
            unset($this->scheduledForRemoval[$objectHash]);
        }
    }
}
