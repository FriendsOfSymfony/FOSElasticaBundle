<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Doctrine\MongoDB;

use FOS\ElasticaBundle\Tests\Unit\Doctrine\AbstractListenerTestCase;

/**
 * @internal
 */
class ListenerTestCase extends AbstractListenerTestCase
{
    protected function setUp(): void
    {
        if (!\class_exists(\Doctrine\ODM\MongoDB\DocumentManager::class)) {
            $this->markTestSkipped('Doctrine MongoDB ODM is not available.');
        }
    }

    protected function getClassMetadataClass()
    {
        return \Doctrine\ODM\MongoDB\Mapping\ClassMetadata::class;
    }

    protected function getLifecycleEventArgsClass()
    {
        return \Doctrine\ODM\MongoDB\Event\LifecycleEventArgs::class;
    }

    protected function getListenerClass()
    {
        return \FOS\ElasticaBundle\Doctrine\Listener::class;
    }

    protected function getObjectManagerClass()
    {
        return \Doctrine\ODM\MongoDB\DocumentManager::class;
    }
}
