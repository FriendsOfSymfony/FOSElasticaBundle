<?php

namespace FOS\ElasticaBundle\Tests\Doctrine\ORM;

use FOS\ElasticaBundle\Tests\Doctrine\AbstractListenerTest;

class ListenerTest extends AbstractListenerTest
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

    protected function getListenerClass()
    {
        return 'FOS\ElasticaBundle\Doctrine\ORM\Listener';
    }

    protected function getObjectManagerClass()
    {
        return 'Doctrine\ORM\EntityManager';
    }
}
