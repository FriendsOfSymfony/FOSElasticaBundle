<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Doctrine\PHPCR;

use FOS\ElasticaBundle\Tests\Doctrine\ListenerTest as BaseListenerTest;

class ListenerTest extends BaseListenerTest
{
    public function setUp()
    {
        if (!class_exists('Doctrine\ODM\PHPCR\DocumentManager')) {
            $this->markTestSkipped('Doctrine PHPCR is not available.');
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
