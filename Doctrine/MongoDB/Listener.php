<?php

namespace FOQ\ElasticaBundle\Doctrine\MongoDB;

use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use FOQ\ElasticaBundle\Doctrine\AbstractListener;

class Listener extends AbstractListener
{
    public function postPersist(LifecycleEventArgs $eventArgs)
    {
        $document = $eventArgs->getDocument();

        if ($document instanceof $this->objectClass && $this->isObjectIndexable($document)) {
            $this->objectPersister->insertOne($document);
        }
    }

    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $document = $eventArgs->getDocument();

        if ($document instanceof $this->objectClass) {
            if ($this->isObjectIndexable($document)) {
                $this->objectPersister->replaceOne($document);
            } else {
                $this->scheduleForRemoval($document, $eventArgs->getDocumentManager());
                $this->removeIfScheduled($document);
            }
        }
    }

    public function preRemove(LifecycleEventArgs $eventArgs)
    {
        $document = $eventArgs->getDocument();

        if ($document instanceof $this->objectClass) {
            $this->scheduleForRemoval($document, $eventArgs->getDocumentManager());
        }
    }

    public function postRemove(LifecycleEventArgs $eventArgs)
    {
        $document = $eventArgs->getDocument();

        if ($document instanceof $this->objectClass) {
            $this->removeIfScheduled($document);
        }
    }
}
