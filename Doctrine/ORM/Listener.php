<?php

namespace FOS\ElasticaBundle\Doctrine\ORM;

use Doctrine\ORM\Event\LifecycleEventArgs;
use FOS\ElasticaBundle\Doctrine\AbstractListener;

class Listener extends AbstractListener
{
    public function postPersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();

        if (get_class($entity) == $this->objectClass && $this->isObjectIndexable($entity)) {
            $this->objectPersister->insertOne($entity);
        }
    }

    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();

        if (get_class($entity) == $this->objectClass) {
            if ($this->isObjectIndexable($entity)) {
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

        if (get_class($entity) == $this->objectClass) {
            $this->scheduleForRemoval($entity, $eventArgs->getEntityManager());
        }
    }

    public function postRemove(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();

        if (get_class($entity) == $this->objectClass) {
            $this->removeIfScheduled($entity);
        }
    }
}
