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
            if ($this->checkMethod && !is_callable(array($document, $this->checkMethod))) {
                if (method_exists($document, $this->checkMethod)) {
                    $exception = sprintf('The specified check method %s::%s is out of scope.', $this->objectClass, $this->checkMethod);
                } else {
                    $exception = sprintf('The specified check method %s::%s does not exist', $this->objectClass, $this->checkMethod);
                }
                throw new \RuntimeException($exception);
            }

            if (($this->checkMethod && call_user_func(array($document, $this->checkMethod))) || !$this->checkMethod) {
                $this->objectPersister->insertOne($document);
            }
        }
    }

    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $document = $eventArgs->getDocument();

        if ($document instanceof $this->objectClass) {

            if ($this->checkMethod && !is_callable(array($document, $this->checkMethod))) {
                if (method_exists($document, $this->checkMethod)) {
                    $exception = sprintf('The specified check method %s::%s is out of scope.', $this->objectClass, $this->checkMethod);
                } else {
                    $exception = sprintf('The specified check method %s::%s does not exist', $this->objectClass, $this->checkMethod);
                }
                throw new \RuntimeException($exception);
            }

            if (($this->checkMethod && call_user_func(array($document, $this->checkMethod))) || !$this->checkMethod) {
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
