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
            if ($this->isIndexableCallback && !is_callable(array($entity, $this->isIndexableCallback))) {
                if (method_exists($entity, $this->isIndexableCallback)) {
                    $exception = sprintf('The specified check method %s::%s is out of scope.', $this->objectClass, $this->isIndexableCallback);
                } else {
                    $exception = sprintf('The specified check method %s::%s does not exist', $this->objectClass, $this->isIndexableCallback);
                }
                throw new \RuntimeException($exception);
            }

            if (($this->isIndexableCallback && call_user_func(array($entity, $this->isIndexableCallback))) || !$this->isIndexableCallback) {
                $this->objectPersister->insertOne($entity);
            }
        }
    }

    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();

        if ($entity instanceof $this->objectClass) {

            if ($this->isIndexableCallback && !is_callable(array($entity, $this->isIndexableCallback))) {
                if (method_exists($entity, $this->isIndexableCallback)) {
                    $exception = sprintf('The specified check method %s::%s is out of scope.', $this->objectClass, $this->isIndexableCallback);
                } else {
                    $exception = sprintf('The specified check method %s::%s does not exist', $this->objectClass, $this->isIndexableCallback);
                }
                throw new \RuntimeException($exception);
            }

            if (($this->isIndexableCallback && call_user_func(array($entity, $this->isIndexableCallback))) || !$this->isIndexableCallback) {
                $this->objectPersister->replaceOne($entity);
            } else {
                $this->scheduleForRemoval($entity, $eventArgs->getEntityManager());
                $this->removeIfScheduled($entity);
            }
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
