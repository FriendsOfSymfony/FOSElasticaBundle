<?php

namespace FOQ\ElasticaBundle\Doctrine\ORM;

use FOQ\ElasticaBundle\Doctrine\AbstractListener;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Common\EventSubscriber;

class Listener extends AbstractListener implements EventSubscriber
{
    public function postPersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();

        if ($entity instanceof $this->objectClass) {
            try {
                $this->objectPersister->insertOne($entity);
            } catch (\Exception $e) {
                $this->logFailure($e->getMessage());
            }
        }
    }

    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();

        if ($entity instanceof $this->objectClass) {
            try {
                $this->objectPersister->replaceOne($entity);
            } catch (\Exception $e) {
                $this->logFailure($e->getMessage());
            }
        }
    }

    public function postRemove(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();

        if ($entity instanceof $this->objectClass) {
            try {
                $this->objectPersister->deleteOne($entity);
            } catch (\Exception $e) {
                $this->logFailure($e->getMessage());
            }
        }
    }
}
