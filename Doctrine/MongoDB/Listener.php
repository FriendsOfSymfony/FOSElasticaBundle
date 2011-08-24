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

        if ($document instanceof $this->objectClass) {
            try {
                $this->objectPersister->insertOne($document);
            } catch (\Exception $e) {
                $this->logFailure($e->getMessage());
            }
        }
    }

    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $document = $eventArgs->getDocument();

        if ($document instanceof $this->objectClass) {
            try {
                $this->objectPersister->replaceOne($document);
            } catch (\Exception $e) {
                $this->logFailure($e->getMessage());
            }
        }
    }

    public function postRemove(LifecycleEventArgs $eventArgs)
    {
        $document = $eventArgs->getDocument();

        if ($document instanceof $this->objectClass) {
            try {
                $this->objectPersister->deleteOne($document);
            } catch (\Exception $e) {
                $this->logFailure($e->getMessage());
            }
        }
    }
}
