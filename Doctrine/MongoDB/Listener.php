<?php

namespace FOQ\ElasticaBundle\Doctrine\MongoDB;

use FOQ\ElasticaBundle\Doctrine\AbstractListener;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\Common\EventSubscriber;

class Listener extends AbstractListener implements EventSubscriber
{
    public function postPersist(LifecycleEventArgs $eventArgs)
    {
        $document = $eventArgs->getDocument();

        if ($document instanceof $this->objectClass && ((method_exists($document, $this->checkMethod) && call_user_func(array($document, $this->checkMethod))) || !$this->checkMethod)) {
            $this->objectPersister->insertOne($document);
        }
    }

    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $document = $eventArgs->getDocument();

        if ($document instanceof $this->objectClass && $this->checkMethod && method_exists($document, $this->checkMethod) && call_user_func(array($document, $this->checkMethod))) {
            $this->objectPersister->replaceOne($document);
        } else if ($document instanceof $this->objectClass) {
            $this->scheduleForRemoval($document, $eventArgs->getDocumentManager());
            $this->removeIfScheduled($document);
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
