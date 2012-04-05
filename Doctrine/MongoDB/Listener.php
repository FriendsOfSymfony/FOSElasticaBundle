<?php

namespace FOQ\ElasticaBundle\Doctrine\MongoDB;

use FOQ\ElasticaBundle\Doctrine\AbstractListener;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\Common\EventSubscriber;

class Listener extends AbstractListener implements EventSubscriber
{
    public function postPersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();

        if ($entity instanceof $this->objectClass && ((method_exists($entity, $this->checkMethod) && call_user_func(array($entity, $this->checkMethod))) || !$this->checkMethod)) {
            $this->objectPersister->insertOne($entity);
        }
    }

    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();

        if ($entity instanceof $this->objectClass && $this->checkMethod && method_exists($entity, $this->checkMethod) && call_user_func(array($entity, $this->checkMethod))) {
            $this->objectPersister->replaceOne($entity);
        } else if ($entity instanceof $this->objectClass) {
            $this->scheduleForRemoval($entity, $eventArgs->getEntityManager());
            $this->removeIfScheduled($entity);
        }
    }

    public function preRemove(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();

        if ($entity instanceof $this->objectClass) {
            $this->scheduleForRemoval($entity, $eventArgs->getEntityManager());
        }
    }

    public function postRemove(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();

        if ($entity instanceof $this->objectClass) {
            $this->removeIfScheduled($entity);
        }
    }
}
