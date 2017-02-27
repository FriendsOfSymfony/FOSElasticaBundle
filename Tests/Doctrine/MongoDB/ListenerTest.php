<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Doctrine\MongoDB;

use FOS\ElasticaBundle\Tests\Doctrine\ListenerTest as BaseListenerTest;

class ListenerTest extends BaseListenerTest
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
        return 'FOS\ElasticaBundle\Doctrine\Listener';
    }

    protected function getObjectManagerClass()
    {
        return 'Doctrine\ODM\MongoDB\DocumentManager';
    }
}
