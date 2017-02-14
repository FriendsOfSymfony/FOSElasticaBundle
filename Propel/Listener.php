<?php

namespace FOS\ElasticaBundle\Propel;

use Glorpen\Propel\PropelBundle\Events\ModelEvent;
use FOS\ElasticaBundle\Doctrine\AbstractListenerBase;
use Glorpen\Propel\PropelBundle\Events\ConnectionEvent;

/**
 * Automatically update ElasticSearch based on changes to the Propel source
 * data. One listener is generated for each Propel entity / ElasticSearch type.
 */
class Listener extends AbstractListenerBase
{
    /**
     * Looks for new objects that should be indexed.
     *
     * @param ModelEvent $eventArgs
     */
    public function onModelInsertPost(ModelEvent $event)
    {
        $this->doPostPersist($event->getModel());
    }

    /**
     * Looks for objects being updated that should be indexed or removed from the index.
     *
     * @param ModelEvent $eventArgs
     */
    public function onModelUpdatePost(ModelEvent $event)
    {
        $this->doPostUpdate($event->getModel());
    }

    /**
     * @param ModelEvent $eventArgs
     */
    public function onModelDeletePre(ModelEvent $event)
    {
        $this->doPreRemove($event->getModel());
    }
    
    public function onConnectionCommitPost(ConnectionEvent $event)
    {
        $this->doPostFlush();
    }
}
