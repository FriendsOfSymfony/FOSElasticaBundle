<?php

declare(strict_types=1);

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Doctrine\ORM;

use FOS\ElasticaBundle\Tests\Unit\Doctrine\AbstractListenerTestCase;

/**
 * @internal
 */
class ListenerTestCase extends AbstractListenerTestCase
{
    protected function getClassMetadataClass(): string
    {
        return \Doctrine\ORM\Mapping\ClassMetadata::class;
    }

    protected function getLifecycleEventArgsClass(): string
    {
        return \Doctrine\Persistence\Event\LifecycleEventArgs::class;
    }

    protected function getListenerClass(): string
    {
        return \FOS\ElasticaBundle\Doctrine\Listener::class;
    }

    protected function getObjectManagerClass(): string
    {
        return \Doctrine\ORM\EntityManager::class;
    }
}
