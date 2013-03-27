<?php

namespace FOS\ElasticaBundle\Tests\Doctrine\MongoDB;

use FOS\ElasticaBundle\Tests\Doctrine\AbstractListenerTest;

class ListenerTest extends AbstractListenerTest
{
    public function setUp()
    {
        if (!class_exists('Doctrine\ODM\MongoDB\DocumentManager')) {
            $this->markTestSkipped('Doctrine MongoDB ODM is not available.');
        }
    }

    protected function getClassMetadataClass()
    {
        return 'Doctrine\ODM\MongoDB\Mapping\ClassMetadata';
    }

    protected function getLifecycleEventArgsClass()
    {
        return 'Doctrine\ODM\MongoDB\Event\LifecycleEventArgs';
    }

    protected function getListenerClass()
    {
        return 'FOS\ElasticaBundle\Doctrine\MongoDB\Listener';
    }

    protected function getObjectManagerClass()
    {
        return 'Doctrine\ODM\MongoDB\DocumentManager';
    }
}
