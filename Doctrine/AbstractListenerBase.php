<?php

namespace FOS\ElasticaBundle\Doctrine;

use FOS\ElasticaBundle\Persister\ObjectPersister;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Provider\IndexableInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Common base for Doctrine / Propel listener.
 */
abstract class AbstractListenerBase
{
    /**
     * Object persister.
     *
     * @var ObjectPersisterInterface
     */
    protected $objectPersister;

    /**
     * Configuration for the listener.
     *
     * @var array
     */
    private $config;

    /**
     * Objects scheduled for insertion.
     *
     * @var array
     */
    public $scheduledForInsertion = array();

    /**
     * Objects scheduled to be updated or removed.
     *
     * @var array
     */
    public $scheduledForUpdate = array();

    /**
     * IDs of objects scheduled for removal.
     *
     * @var array
     */
    public $scheduledForDeletion = array();

    /**
     * PropertyAccessor instance.
     *
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    /**
     * @var IndexableInterface
     */
    private $indexable;

    /**
     * Constructor.
     *
     * @param ObjectPersisterInterface $objectPersister
     * @param IndexableInterface       $indexable
     * @param array                    $config
     * @param LoggerInterface          $logger
     */
    public function __construct(
        ObjectPersisterInterface $objectPersister,
        IndexableInterface $indexable,
        array $config = array(),
        LoggerInterface $logger = null
    ) {
        $this->config = array_merge(array(
            'identifier' => 'id',
        ), $config);
        $this->indexable = $indexable;
        $this->objectPersister = $objectPersister;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();

        if ($logger && $this->objectPersister instanceof ObjectPersister) {
            $this->objectPersister->setLogger($logger);
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
     * Iterating through scheduled actions *after* flushing ensures that the
     * ElasticSearch index will be affected only if the query is successful.
     */
    protected function doPostFlush()
    {
        $this->persistScheduled();
    }

    /**
     * Record the specified identifier to delete. Do not need to entire object.
     *
     * @param object $object
     */
    private function scheduleForDeletion($object)
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
    
    /**
     * Looks for new objects that should be indexed.
     *
     * @param object $entity
     */
    protected function doPostPersist($entity)
    {
        if ($this->objectPersister->handlesObject($entity) && $this->isObjectIndexable($entity)) {
            $this->scheduledForInsertion[] = $entity;
        }
    }
    
    /**
     * Looks for objects being updated that should be indexed or removed from the index.
     *
     * @param object $entity
     */
    protected function doPostUpdate($entity)
    {
        if ($this->objectPersister->handlesObject($entity)) {
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
     * preRemove, first check that the entity is managed by persister.
     *
     * @param object $entity
     */
    protected function doPreRemove($entity)
    {
        if ($this->objectPersister->handlesObject($entity)) {
            $this->scheduleForDeletion($entity);
        }
    }
}
