<?php

namespace FOS\ElasticaBundle\Doctrine\ORM;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use FOS\ElasticaBundle\Doctrine\AbstractListener;

class Listener extends AbstractListener
{
    /** @var array */
    protected $changes = array();

    public function postPersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();

        if ($entity instanceof $this->objectClass && $this->isObjectIndexable($entity)) {
            $this->objectPersister->insertOne($entity);
        }
    }

    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();

        if ($entity instanceof $this->objectClass) {
            if ($this->isObjectIndexable($entity)) {
                $this->changes[$this->changeKey($entity)] = $eventArgs->getEntityChangeSet();
            }
        }
    }

    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();

        if ($entity instanceof $this->objectClass) {
            if ($this->isObjectIndexable($entity)) {
                $this->objectPersister->replaceOne($entity, $this->changes[$this->changeKey($entity)]);
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

    protected function changeKey($entity)
    {
        return get_class($entity) . '_' . $entity->getId();
    }
}
