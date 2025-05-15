<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Doctrine;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use FOS\ElasticaBundle\Doctrine\ConditionalUpdate;
use FOS\ElasticaBundle\Doctrine\Listener;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Provider\IndexableInterface;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class ConditionalUpdateListenerTest extends TestCase
{
    public function testEntityWithConditionalUpdateTrueIsIndexed()
    {
        $entity = $this->createMock(ConditionalUpdate::class);
        $entity->expects($this->once())
            ->method('shouldBeUpdated')
            ->willReturn(true)
        ;

        $persister = $this->createMock(ObjectPersisterInterface::class);
        $persister->expects($this->once())
            ->method('handlesObject')
            ->with($entity)
            ->willReturn(true)
        ;

        $indexable = $this->createMock(IndexableInterface::class);
        $indexable->expects($this->once())
            ->method('isObjectIndexable')
            ->with('index_name', $entity)
            ->willReturn(true)
        ;

        $eventArgs = $this->createMock(LifecycleEventArgs::class);
        $eventArgs->expects($this->once())
            ->method('getObject')
            ->willReturn($entity)
        ;

        $listener = new Listener($persister, $indexable, ['indexName' => 'index_name']);

        $listener->postPersist($eventArgs);

        $this->assertContains($entity, $listener->scheduledForInsertion);
    }

    public function testEntityWithConditionalUpdateFalseIsNotIndexed()
    {
        // Create a mock entity implementing ConditionalUpdate that returns false
        $entity = $this->createMock(ConditionalUpdate::class);
        $entity->expects($this->once())
            ->method('shouldBeUpdated')
            ->willReturn(false)
        ;

        // Mock dependencies
        $persister = $this->createMock(ObjectPersisterInterface::class);
        $persister->expects($this->once())
            ->method('handlesObject')
            ->with($entity)
            ->willReturn(true)
        ;

        $indexable = $this->createMock(IndexableInterface::class);
        $indexable->expects($this->once())
            ->method('isObjectIndexable')
            ->with('index_name', $entity)
            ->willReturn(true)
        ;

        // Create the event args
        $eventArgs = $this->createMock(LifecycleEventArgs::class);
        $eventArgs->expects($this->once())
            ->method('getObject')
            ->willReturn($entity)
        ;

        // Create listener
        $listener = new Listener($persister, $indexable, ['indexName' => 'index_name']);

        // Test postPersist
        $listener->postPersist($eventArgs);

        // Check if entity is NOT in scheduledForInsertion
        $this->assertEmpty($listener->scheduledForInsertion);
    }

    public function testEntityWithConditionalUpdateTrueIsUpdated()
    {
        // Create a mock entity implementing ConditionalUpdate that returns true
        $entity = $this->createMock(ConditionalUpdate::class);
        $entity->expects($this->once())
            ->method('shouldBeUpdated')
            ->willReturn(true)
        ;

        // Mock dependencies
        $persister = $this->createMock(ObjectPersisterInterface::class);
        $persister->expects($this->once())
            ->method('handlesObject')
            ->with($entity)
            ->willReturn(true)
        ;

        $indexable = $this->createMock(IndexableInterface::class);
        $indexable->expects($this->once())
            ->method('isObjectIndexable')
            ->with('index_name', $entity)
            ->willReturn(true)
        ;

        // Create the event args
        $eventArgs = $this->createMock(LifecycleEventArgs::class);
        $eventArgs->expects($this->once())
            ->method('getObject')
            ->willReturn($entity)
        ;

        // Create listener
        $listener = new Listener($persister, $indexable, ['indexName' => 'index_name']);

        // Test postUpdate
        $listener->postUpdate($eventArgs);

        // Check if entity is in scheduledForUpdate
        $this->assertContains($entity, $listener->scheduledForUpdate);
    }

    public function testEntityWithConditionalUpdateFalseIsNotUpdated()
    {
        // Create a mock entity implementing ConditionalUpdate that returns false
        $entity = $this->createMock(ConditionalUpdate::class);
        $entity->expects($this->once())
            ->method('shouldBeUpdated')
            ->willReturn(false)
        ;

        // Mock dependencies
        $persister = $this->createMock(ObjectPersisterInterface::class);
        $persister->expects($this->once())
            ->method('handlesObject')
            ->with($entity)
            ->willReturn(true)
        ;

        $indexable = $this->createMock(IndexableInterface::class);
        $indexable->expects($this->once())
            ->method('isObjectIndexable')
            ->with('index_name', $entity)
            ->willReturn(true)
        ;

        // Create the event args
        $eventArgs = $this->createMock(LifecycleEventArgs::class);
        $eventArgs->expects($this->once())
            ->method('getObject')
            ->willReturn($entity)
        ;

        // Create listener
        $listener = new Listener($persister, $indexable, ['indexName' => 'index_name']);

        // Test postUpdate
        $listener->postUpdate($eventArgs);

        // Check if entity is NOT in scheduledForUpdate
        $this->assertEmpty($listener->scheduledForUpdate);
    }
}
