<?php

namespace FOS\ElasticaBundle\Tests\Doctrine\ORM;

use FOS\ElasticaBundle\Tests\Doctrine\AbstractListenerTest as BaseListenerTest;

class ListenerTest extends BaseListenerTest
{
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
        return 'FOS\ElasticaBundle\Doctrine\Listener';
    }

    protected function getObjectManagerClass()
    {
        return 'Doctrine\ORM\EntityManager';
    }
}
