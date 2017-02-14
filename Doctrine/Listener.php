<?php

namespace FOS\ElasticaBundle\Doctrine;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

/**
 * Automatically update ElasticSearch based on changes to the Doctrine source
 * data. One listener is generated for each Doctrine entity / ElasticSearch type.
 */
class Listener extends AbstractListenerBase
{
    /**
     * Looks for new objects that should be indexed.
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function postPersist(LifecycleEventArgs $eventArgs)
    {
        $this->doPostPersist($eventArgs->getObject());
    }

    /**
     * Looks for objects being updated that should be indexed or removed from the index.
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $this->doPostUpdate($eventArgs->getObject());
    }

    /**
     * Delete objects preRemove instead of postRemove so that we have access to the id.  Because this is called
     * preRemove, first check that the entity is managed by Doctrine.
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function preRemove(LifecycleEventArgs $eventArgs)
    {
        $this->doPreRemove($eventArgs->getObject());
    }

    /**
     * Iterate through scheduled actions before flushing to emulate 2.x behavior.
     * Note that the ElasticSearch index will fall out of sync with the source
     * data in the event of a crash during flush.
     *
     * This method is only called in legacy configurations of the listener.
     *
     * @deprecated This method should only be called in applications that depend
     *             on the behaviour that entities are indexed regardless of if a
     *             flush is successful.
     */
    public function preFlush()
    {
        $this->doPostFlush();
    }

    /**
     * Iterating through scheduled actions *after* flushing ensures that the
     * ElasticSearch index will be affected only if the query is successful.
     */
    public function postFlush()
    {
        $this->doPostFlush();
    }
}
