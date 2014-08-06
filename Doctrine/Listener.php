<?php

namespace FOS\ElasticaBundle\Doctrine;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventSubscriber;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Persister\ObjectPersister;
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
     * Object persister
     *
     * @var ObjectPersister
     */
    protected $objectPersister;

    /**
     * List of subscribed events
     *
     * @var array
     */
    protected $events;

    /**
     * Configuration for the listener
     *
     * @var string
     */
    private $config;

    /**
     * Objects scheduled for insertion and replacement
     */
    public $scheduledForInsertion = array();
    public $scheduledForUpdate = array();

    /**
     * IDs of objects scheduled for removal
     */
    public $scheduledForDeletion = array();

    /**
     * PropertyAccessor instance
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
    ) {
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
     * Provides unified method for retrieving a doctrine object from an EventArgs instance
     *
     * @param   EventArgs           $eventArgs
     * @return  object              Entity | Document
     * @throws  \RuntimeException   if no valid getter is found.
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

    /**
     * Provides unified method for retrieving a doctrine object manager from an EventArgs instance
     *
     * @param   EventArgs           $eventArgs
     * @return  ObjectManager       An instance implementing ObjectManager
     * @throws  \RuntimeException   if no valid getter is found.
     */
    private function getObjectManager(EventArgs $eventArgs)
    {
        if (method_exists($eventArgs, 'getObjectManager')) {
            return $eventArgs->getObjectManager();
        } elseif (method_exists($eventArgs, 'getEntityManager')) {
            return $eventArgs->getEntityManager();
        } elseif (method_exists($eventArgs, 'getDocumentManager')) {
            return $eventArgs->getDocumentManager();
        }

        throw new \RuntimeException('Unable to retrieve object manager from EventArgs.');
    }

    /**
     * Handles newly created entities that have been persisted to the database
     * The postPersist event must be used so newly persisted entities have their identifier value
     *
     * @param   EventArgs           $eventArgs
     * @return  void
     */
    public function postPersist(EventArgs $eventArgs)
    {
        $entity = $this->getDoctrineObject($eventArgs);
        $this->scheduleForInsertion($entity);
    }

    /**
     * Handles updated entities
     *
     * @param   EventArgs           $eventArgs
     * @return  void
     */
    public function postUpdate(EventArgs $eventArgs)
    {
        $entity = $this->getDoctrineObject($eventArgs);
        $this->scheduleForUpdate($entity);
    }

    /**
     * Delete objects preRemove instead of postRemove so that we have access to the id.  Because this is called
     * preRemove, first check that the entity is managed by Doctrine
     */
    public function preRemove(EventArgs $eventArgs)
    {
        $entity = $this->getDoctrineObject($eventArgs);
        $this->scheduleForDeletion($entity);
    }

    /**
     * Persist scheduled objects to ElasticSearch
     * After persisting, clear the scheduled queue to prevent multiple data updates when using multiple flush calls
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
     * Iterate through scheduled actions before flushing to emulate 2.x behavior.  Note that the ElasticSearch index
     * will fall out of sync with the source data in the event of a crash during flush.
     */
    public function preFlush(EventArgs $eventArgs)
    {
        $this->persistScheduled();
    }

    /**
     * Iterating through scheduled actions *after* flushing ensures that the ElasticSearch index will be affected
     * only if the query is successful
     */
    public function postFlush(EventArgs $eventArgs)
    {
        $this->scheduleObjectsWithCollectionChanges($eventArgs);
        $this->persistScheduled();
    }

    /**
     * Provides a unified method for scheduling Doctrine objects with collection changes (e.g. ReferenceMany) to be
     * updated in Elasticsearch
     *
     * Note: The PersistentCollection class for both the Doctrine ORM and the MongoDB ODM contain the 'getOwner'
     * method. Be that as it may, the Doctrine\Common\Collections\Collection interface does not strictly
     * require/define this method. As such, the method_exists() check is used
     *
     * @param   EventArgs           $eventArgs
     * @return  UnitOfWork          An instance implementing ObjectManager
     */
    private function scheduleObjectsWithCollectionChanges(EventArgs $eventArgs)
    {
        $collectionChanges = $this->getCollectionChanges($eventArgs);
        foreach ($collectionChanges as $collection) {
            $this->scheduleForUpdate($collection->getOwner());
        }
    }

    /**
     * Provides a unified method for retrieving a set of collection changes from the Doctrine UnitOfWork
     *
     * Note: The UnitOfWork class for both the Doctrine ORM and the MongoDB ODM contain these methods.
     * Be that as it may, no Doctrine\Common interface exists for the UnitOfWork, so these methods are not strictly
     * required/defined. As such, the method_exists() check is used
     *
     * @param   EventArgs           $eventArgs
     * @return  UnitOfWork          An instance implementing ObjectManager
     * @throws  \RuntimeException   if no valid getter is found.
     */
    private function getCollectionChanges(EventArgs $eventArgs)
    {
        $objectManager = $this->getObjectManager($eventArgs);
        $uow = $objectManager->getUnitOfWork();

        // Merge updates (adds, removes) and deletes (entire collection removals) and return
        $changes = array_merge($uow->getScheduledCollectionUpdates(), $uow->getScheduledCollectionDeletions());
        return array_filter($changes, function($collection) {
            if ($collection instanceof \Doctrine\ORM\PersistentCollection) {
                return true;
            }
            if ($collection instanceof \Doctrine\ODM\MongoDB\PersistentCollection) {
                return true;
            }
            return false;
        });
    }

    /**
     * Schedules a Doctrine object (entity/document) to be updated in Elasticsearch
     *
     * @param  mixed  $object
     * @return void
     */
    private function scheduleForUpdate($object)
    {
        if ($this->objectPersister->handlesObject($object)) {
            if ($this->isObjectIndexable($object)) {
                $oid = spl_object_hash($object);
                $this->scheduledForUpdate[$oid] = $object;
            } else {
                // Delete if no longer indexable
                $this->scheduleForDeletion($object);
            }
        }
    }

    /**
     * Schedules a Doctrine object (entity/document) for insertion into Elasticsearch
     *
     * @param  mixed  $object
     * @return void
     */
    private function scheduleForInsertion($object)
    {
        if ($this->objectPersister->handlesObject($object) && $this->isObjectIndexable($object)) {
            $oid = spl_object_hash($object);
            $this->scheduledForInsertion[$oid] = $object;
        }
    }

    /**
     * Record the specified identifier to delete. Do not need to entire object.
     * @param  mixed  $object
     * @return mixed
     */
    private function scheduleForDeletion($object)
    {
        if ($this->objectPersister->handlesObject($object)) {
            if ($identifierValue = $this->propertyAccessor->getValue($object, $this->config['identifier'])) {
                $oid = spl_object_hash($object);
                $this->scheduledForDeletion[$oid] = $identifierValue;
            }
        }
    }

    /**
     * Checks if the object is indexable or not.
     *
     * @param object $object
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
