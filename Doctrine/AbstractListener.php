<?php

namespace FOQ\ElasticaBundle\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\ObjectManager;
use FOQ\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOQ\ElasticaBundle\Persister\ObjectPersister;

abstract class AbstractListener implements EventSubscriber
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
     * Name of domain model field used as the ES identifier
     *
     * @var string
     */
    protected $esIdentifierField;

    /**
     * Callback for determining if an object should be indexed
     *
     * @var mixed
     */
    protected $isIndexableCallback;

    /**
     * Objects scheduled for removal
     *
     * @var array
     */
    private $scheduledForRemoval = array();

    /**
     * Constructor.
     *
     * @param ObjectPersisterInterface $objectPersister
     * @param string                   $objectClass
     * @param array                    $events
     * @param string                   $esIdentifierField
     */
    public function __construct(ObjectPersisterInterface $objectPersister, $objectClass, array $events, $esIdentifierField = 'id')
    {
        $this->objectPersister     = $objectPersister;
        $this->objectClass         = $objectClass;
        $this->events              = $events;
        $this->esIdentifierField   = $esIdentifierField;
    }

    /**
     * @see Doctrine\Common\EventSubscriber::getSubscribedEvents()
     */
    public function getSubscribedEvents()
    {
        return $this->events;
    }

    /**
     * Set the callback for determining object index eligibility.
     *
     * If callback is a string, it must be public method on the object class
     * that expects no arguments and returns a boolean. Otherwise, the callback
     * should expect the object for consideration as its only argument and
     * return a boolean.
     *
     * @param callback $callback
     * @throws \RuntimeException if the callback is not callable
     */
    public function setIsIndexableCallback($callback)
    {
        if (is_string($callback)) {
            if (!is_callable(array($this->objectClass, $callback))) {
                throw new \RuntimeException(sprintf('Indexable callback %s::%s() is not callable.', $this->objectClass, $callback));
            }
        } elseif (!is_callable($callback)) {
            if (is_array($callback)) {
                list($class, $method) = $callback + array(null, null);
                if (is_object($class)) {
                    $class = get_class($class);
                }
                if ($class && $method) {
                    throw new \RuntimeException(sprintf('Indexable callback %s::%s() is not callable.', $class, $method));
                }
            }
            throw new \RuntimeException('Indexable callback is not callable.');
        }

        $this->isIndexableCallback = $callback;
    }

    /**
     * Return whether the object is indexable with respect to the callback.
     *
     * @param object $object
     * @return boolean
     */
    protected function isObjectIndexable($object)
    {
        if (!$this->isIndexableCallback) {
            return true;
        }

        return is_string($this->isIndexableCallback)
            ? call_user_func(array($object, $this->isIndexableCallback))
            : call_user_func($this->isIndexableCallback, $object);
    }

    /**
     * Schedules the object for removal.
     *
     * This is usually called during the pre-remove event.
     *
     * @param object        $object
     * @param ObjectManager $objectManager
     */
    protected function scheduleForRemoval($object, ObjectManager $objectManager)
    {
        $metadata = $objectManager->getClassMetadata($this->objectClass);
        $esId = $metadata->getFieldValue($object, $this->esIdentifierField);
        $this->scheduledForRemoval[spl_object_hash($object)] = $esId;
    }

    /**
     * Removes the object if it was scheduled for removal.
     *
     * This is usually called during the post-remove event.
     *
     * @param object $object
     */
    protected function removeIfScheduled($object)
    {
        $objectHash = spl_object_hash($object);
        if (isset($this->scheduledForRemoval[$objectHash])) {
            $this->objectPersister->deleteById($this->scheduledForRemoval[$objectHash]);
            unset($this->scheduledForRemoval[$objectHash]);
        }
    }
}
