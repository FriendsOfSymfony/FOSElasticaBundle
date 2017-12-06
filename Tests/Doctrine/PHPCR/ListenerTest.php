<?php

namespace FOS\ElasticaBundle\Tests\Doctrine\PHPCR;

use FOS\ElasticaBundle\Tests\Doctrine\ListenerTest as BaseListenerTest;
use Doctrine\ODM\PHPCR\DocumentManager;

class ListenerTest extends BaseListenerTest
{
    public function setUp()
    {
        if (!class_exists(DocumentManager::class)) {
            $this->markTestSkipped('Doctrine PHPCR is not present');
        }
    }

    protected function getClassMetadataClass()
    {
        return 'Doctrine\ODM\PHPCR\Mapping\ClassMetadata';
    }

    protected function getLifecycleEventArgsClass()
    {
        return 'Doctrine\Common\Persistence\Event\LifecycleEventArgs';
    }

    protected function getListenerClass()
    {
        return 'FOS\ElasticaBundle\Doctrine\Listener';
    }

    protected function getObjectManagerClass()
    {
        return 'Doctrine\ODM\PHPCR\DocumentManager';
    }
}
