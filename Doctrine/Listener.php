<?php

namespace FOS\ElasticaBundle\Doctrine;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventSubscriber;
use FOS\ElasticaBundle\Persister\ObjectPersister;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Provider\IndexableInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Automatically update ElasticSearch based on changes to the Doctrine source
 * data. One listener is generated for each Doctrine entity / ElasticSearch type.
 */
class Listener implements EventSubscriber
{
    /**
     * Object persister.
     *
     * @var ObjectPersister
     */
    protected $objectPersister;

    /**
     * List of subscribed events.
     *
     * @var array
     */
    protected $events;

    /**
     * Configuration for the listener.
     *
     * @var string
     */
    private $config;

    /**
     * Objects scheduled for insertion and replacement.
     */
    public $scheduledForInsertion = array();
    public $scheduledForUpdate = array();

    /**
     * IDs of objects scheduled for removal.
     */
    public $scheduledForDeletion = array();

    /**
     * PropertyAccessor instance.
     *
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    /**
     * @var \FOS\ElasticaBundle\Provider\IndexableInterface
     */
    private $indexable;

    /**
     * Constructor.
     *
     * @param ObjectPersisterInterface $objectPersister
     * @param array $events
     * @param IndexableInterface $indexable
     * @param array $config
     * @param null $logger
     */
    public function __construct(
        ObjectPersisterInterface $objectPersister,
        array $events,
        IndexableInterface $indexable,
        array $config = array(),
        $logger = null
    )
    {
        $this->config = array_merge(array(
            'identifier' => 'id',
        ), $config);
        $this->events = $events;
        $this->indexable = $indexable;
        $this->objectPersister = $objectPersister;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();

        if ($logger) {
            $this->objectPersister->setLogger($logger);
        }
    }

    /**
     * @see Doctrine\Common\EventSubscriber::getSubscribedEvents()
     */
    public function getSubscribedEvents()
    {
        return $this->events;
    }

    /**
     * Provides unified method for retrieving a doctrine object from an EventArgs instance.
     *
     * @param EventArgs $eventArgs
     *
     * @return object Entity | Document
     *
     * @throws \RuntimeException if no valid getter is found.
     */
    private function getDoctrineObject(EventArgs $eventArgs)
    {
        if (method_exists($eventArgs, 'getObject')) {
            return $eventArgs->getObject();
        } elseif (method_exists($eventArgs, 'getEntity')) {
            return $eventArgs->getEntity();
        } elseif (method_exists($eventArgs, 'getDocument')) {
            return $eventArgs->getDocument();
        }

        throw new \RuntimeException('Unable to retrieve object from EventArgs.');
    }

    public function postPersist(EventArgs $eventArgs)
    {
        $entity = $this->getDoctrineObject($eventArgs);

        if ($this->objectPersister->handlesObject($entity) && $this->isObjectIndexable($entity)) {
            $this->scheduledForInsertion[] = $entity;
        }
    }

    public function postUpdate(EventArgs $eventArgs)
    {
        $entity = $this->getDoctrineObject($eventArgs);

        if ($this->objectPersister->handlesObject($entity)
            && (!isset($entity->needUpdateIndex) || $entity->needUpdateIndex)||
            (isset($entity->__updateStatus) && $entity->__updateStatus)
        ) {
            if ($this->isObjectIndexable($entity)) {
                $this->scheduledForUpdate[] = $entity;
            } else {
                // Delete if no longer indexable
                $this->scheduleForDeletion($entity);
            }
        }
    }

    /**
     * Delete objects preRemove instead of postRemove so that we have access to the id.  Because this is called
     * preRemove, first check that the entity is managed by Doctrine.
     */
    public function preRemove(EventArgs $eventArgs)
    {
        $entity = $this->getDoctrineObject($eventArgs);

        if ($this->objectPersister->handlesObject($entity)) {
            $this->scheduleForDeletion($entity);
        }
    }

    /**
     * Persist scheduled objects to ElasticSearch
     * After persisting, clear the scheduled queue to prevent multiple data updates when using multiple flush calls.
     */
    private function persistScheduled()
    {
        if (count($this->scheduledForInsertion)) {
            $this->objectPersister->insertMany($this->scheduledForInsertion);
            $this->scheduledForInsertion = array();
        }
        if (count($this->scheduledForUpdate)) {
            $this->objectPersister->replaceMany($this->scheduledForUpdate);
            $this->scheduledForUpdate = array();
        }
        if (count($this->scheduledForDeletion)) {
            $this->objectPersister->deleteManyByIdentifiers($this->scheduledForDeletion);
            $this->scheduledForDeletion = array();
        }
    }

    /**
     * Iterate through scheduled actions before flushing to emulate 2.x behavior.
     * Note that the ElasticSearch index will fall out of sync with the source
     * data in the event of a crash during flush.
     *
     * This method is only called in legacy configurations of the listener.
     */
    public function preFlush()
    {
        $this->persistScheduled();
    }

    /**
     * Iterating through scheduled actions *after* flushing ensures that the
     * ElasticSearch index will be affected only if the query is successful.
     */
    public function postFlush()
    {
        $this->persistScheduled();
    }

    /**
     * Record the specified identifier to delete. Do not need to entire object.
     *
     * @param object $object
     */
    protected function scheduleForDeletion($object)
    {
        if ($identifierValue = $this->propertyAccessor->getValue($object, $this->config['identifier'])) {
            $this->scheduledForDeletion[] = $identifierValue;
        }
    }

    /**
     * Checks if the object is indexable or not.
     *
     * @param object $object
     *
     * @return bool
     */
    private function isObjectIndexable($object)
    {
        return $this->indexable->isObjectIndexable(
            $this->config['indexName'],
            $this->config['typeName'],
            $object
        );
    }
}
