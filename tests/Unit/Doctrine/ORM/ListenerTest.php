<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Doctrine\ORM;

use FOS\ElasticaBundle\Tests\Unit\Doctrine\AbstractListenerTest;

/**
 * @internal
 */
class ListenerTest extends AbstractListenerTest
{
    protected function getClassMetadataClass()
    {
        return \Doctrine\ORM\Mapping\ClassMetadata::class;
    }

    protected function getLifecycleEventArgsClass()
    {
        return \Doctrine\Persistence\Event\LifecycleEventArgs::class;
    }

    protected function getListenerClass()
    {
        return \FOS\ElasticaBundle\Doctrine\Listener::class;
    }

    protected function getObjectManagerClass()
    {
        return \Doctrine\ORM\EntityManager::class;
    }
}
