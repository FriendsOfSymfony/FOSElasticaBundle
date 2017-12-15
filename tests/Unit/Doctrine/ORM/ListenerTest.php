<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Doctrine\ORM;

use FOS\ElasticaBundle\Tests\Unit\Doctrine\ListenerTest as BaseListenerTest;

class ListenerTest extends BaseListenerTest
{
    protected function getClassMetadataClass()
    {
        return \Doctrine\ORM\Mapping\ClassMetadata::class;
    }

    protected function getLifecycleEventArgsClass()
    {
        return \Doctrine\ORM\Event\LifecycleEventArgs::class;
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
