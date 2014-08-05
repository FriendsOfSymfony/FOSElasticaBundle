<?php

namespace FOS\ElasticaBundle\Tests\Doctrine\ORM;

use FOS\ElasticaBundle\Tests\Doctrine\ListenerTest as BaseListenerTest;

class ListenerTest extends BaseListenerTest
{
    public function setUp()
    {
        if (!class_exists('Doctrine\ORM\EntityManager')) {
            $this->markTestSkipped('Doctrine ORM is not available.');
        }
    }

    protected function getClassMetadataClass()
    {
        return 'Doctrine\ORM\Mapping\ClassMetadata';
    }

    protected function getLifecycleEventArgsClass()
    {
        return 'Doctrine\ORM\Event\LifecycleEventArgs';
    }

    protected function getPostFlushEventArgsClass()
    {
        return 'Doctrine\ORM\Event\PostFlushEventArgs';
    }

    protected function getListenerClass()
    {
        return 'FOS\ElasticaBundle\Doctrine\Listener';
    }

    protected function getObjectManagerClass()
    {
        return 'Doctrine\ORM\EntityManager';
    }

    protected function getUnitOfWorkClass()
    {
        return 'Doctrine\ORM\UnitOfWork';
    }
}
