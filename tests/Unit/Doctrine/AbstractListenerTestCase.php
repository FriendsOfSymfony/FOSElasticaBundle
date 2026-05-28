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

use Elastica\Index;
use FOS\ElasticaBundle\Persister\ObjectPersister;
use FOS\ElasticaBundle\Provider\IndexableInterface;
use PHPUnit\Framework\TestCase;

class Entity
{
    public $identifier;

    public function __construct(private readonly int $id)
    {
    }

    public function getId(): int
    {
        return $this->id;
    }
}

class ConditionalUpdateEntity extends Entity
{
    public function __construct($id, private $shouldBeUpdated)
    {
    }

    public function shouldBeUpdated(): bool
    {
        return $this->shouldBeUpdated;
    }
}

/**
 * See concrete MongoDB/ORM instances of this abstract test.
 *
 * @author Richard Miller <info@limethinking.co.uk>
 */
abstract class AbstractListenerTestCase extends TestCase
{
    public function testObjectInsertedOnPersist(): void
    {
        $entity = new Entity(1);
        $persister = $this->getMockPersister($entity, 'index');
        $eventArgs = $this->createLifecycleEventArgs($entity, $this->getMockObjectManager());
        $indexable = $this->getMockIndexable('index', $entity, true);

        $listener = $this->createListener($persister, $indexable, ['indexName' => 'index']);
        $listener->postPersist($eventArgs);

        $this->assertSame($entity, \current($listener->scheduledForInsertion));

        $persister->expects($this->once())
            ->method('insertMany')
            ->with($listener->scheduledForInsertion)
        ;

        $listener->postFlush($eventArgs);
    }

    public function testPersistDeferred(): void
    {
        $entity = new Entity(1);
        $persister = $this->getMockPersister($entity, 'index');
        $eventArgs = $this->createLifecycleEventArgs($entity, $this->getMockObjectManager());
        $indexable = $this->getMockIndexable('index', $entity, true);

        $listener = $this->createListener($persister, $indexable, ['indexName' => 'index', 'defer' => true]);
        $listener->postPersist($eventArgs);

        $this->assertSame($entity, \current($listener->scheduledForInsertion));

        $persister->expects($this->never())->method('insertMany');

        $listener->postFlush($eventArgs);
    }

    public function testNonIndexableObjectNotInsertedOnPersist(): void
    {
        $entity = new Entity(1);
        $persister = $this->getMockPersister($entity, 'index');
        $eventArgs = $this->createLifecycleEventArgs($entity, $this->getMockObjectManager());
        $indexable = $this->getMockIndexable('index', $entity, false);

        $listener = $this->createListener($persister, $indexable, ['indexName' => 'index']);
        $listener->postPersist($eventArgs);

        $this->assertEmpty($listener->scheduledForInsertion);

        $persister->expects($this->never())
            ->method('insertOne')
        ;
        $persister->expects($this->never())
            ->method('insertMany')
        ;

        $listener->postFlush($eventArgs);
    }

    public function testObjectReplacedOnUpdate(): void
    {
        $entity = new Entity(1);
        $persister = $this->getMockPersister($entity, 'index');
        $eventArgs = $this->createLifecycleEventArgs($entity, $this->getMockObjectManager());
        $indexable = $this->getMockIndexable('index', $entity, true);

        $listener = $this->createListener($persister, $indexable, ['indexName' => 'index']);
        $listener->postUpdate($eventArgs);

        $this->assertSame($entity, \current($listener->scheduledForUpdate));

        $persister->expects($this->once())
            ->method('replaceMany')
            ->with([$entity])
        ;
        $persister->expects($this->never())
            ->method('deleteById')
        ;

        $listener->postFlush($eventArgs);
    }

    public function testNonIndexableObjectRemovedOnUpdate(): void
    {
        $classMetadata = $this->getMockClassMetadata();
        $objectManager = $this->getMockObjectManager();

        $entity = new Entity(1);
        $persister = $this->getMockPersister($entity, 'index');
        $eventArgs = $this->createLifecycleEventArgs($entity, $objectManager);
        $indexable = $this->getMockIndexable('index', $entity, false);

        $objectManager->expects($this->any())
            ->method('getClassMetadata')
            ->with($entity::class)
            ->will($this->returnValue($classMetadata))
        ;

        $classMetadata->expects($this->any())
            ->method('getFieldValue')
            ->with($entity, 'id')
            ->will($this->returnValue($entity->getId()))
        ;

        $listener = $this->createListener($persister, $indexable, ['indexName' => 'index']);
        $listener->postUpdate($eventArgs);

        $this->assertEmpty($listener->scheduledForUpdate);
        $this->assertSame($entity->getId(), \current($listener->scheduledForDeletion));

        $persister->expects($this->never())
            ->method('replaceOne')
        ;
        $persister->expects($this->once())
            ->method('deleteManyByIdentifiers')
            ->with([$entity->getId()])
        ;

        $listener->postFlush($eventArgs);
    }

    public function testObjectDeletedOnRemove(): void
    {
        $classMetadata = $this->getMockClassMetadata();
        $objectManager = $this->getMockObjectManager();

        $entity = new Entity(1);
        $persister = $this->getMockPersister($entity, 'index');
        $eventArgs = $this->createLifecycleEventArgs($entity, $objectManager);
        $indexable = $this->getMockIndexable('index', $entity);

        $objectManager->expects($this->any())
            ->method('getClassMetadata')
            ->with($entity::class)
            ->will($this->returnValue($classMetadata))
        ;

        $classMetadata->expects($this->any())
            ->method('getFieldValue')
            ->with($entity, 'id')
            ->will($this->returnValue($entity->getId()))
        ;

        $listener = $this->createListener($persister, $indexable, ['indexName' => 'index']);
        $listener->preRemove($eventArgs);

        $this->assertSame($entity->getId(), \current($listener->scheduledForDeletion));

        $persister->expects($this->once())
            ->method('deleteManyByIdentifiers')
            ->with([$entity->getId()])
        ;

        $listener->postFlush($eventArgs);
    }

    public function testObjectWithNonStandardIdentifierDeletedOnRemove(): void
    {
        $classMetadata = $this->getMockClassMetadata();
        $objectManager = $this->getMockObjectManager();

        $entity = new Entity(1);
        $entity->identifier = 'foo';
        $persister = $this->getMockPersister($entity, 'index');
        $eventArgs = $this->createLifecycleEventArgs($entity, $objectManager);
        $indexable = $this->getMockIndexable('index', $entity);

        $objectManager->expects($this->any())
            ->method('getClassMetadata')
            ->with($entity::class)
            ->will($this->returnValue($classMetadata))
        ;

        $classMetadata->expects($this->any())
            ->method('getFieldValue')
            ->with($entity, 'identifier')
            ->will($this->returnValue($entity->getId()))
        ;

        $listener = $this->createListener($persister, $indexable, ['identifier' => 'identifier', 'indexName' => 'index']);
        $listener->preRemove($eventArgs);

        $this->assertSame($entity->identifier, \current($listener->scheduledForDeletion));

        $persister->expects($this->once())
            ->method('deleteManyByIdentifiers')
            ->with([$entity->identifier])
        ;

        $listener->postFlush($eventArgs);
    }

    public function testShouldPersistOnKernelTerminateIfDeferIsTrue(): void
    {
        $entity = new Entity(1);
        $persister = $this->getMockPersister($entity, 'index');
        $indexable = $this->getMockIndexable(null);
        $listener = $this->createListener(
            $persister,
            $indexable,
            ['identifier' => 'identifier', 'indexName' => 'index', 'defer' => true]
        );
        $scheduledForInsertion = ['data'];
        $refListener = new \ReflectionObject($listener);
        $refScheduledForInsertion = $refListener->getProperty('scheduledForInsertion');
        $refScheduledForInsertion->setValue($listener, $scheduledForInsertion);
        $persister->expects($this->once())->method('insertMany')->with($scheduledForInsertion);

        $listener->onTerminate();
    }

    public function testConditionalUpdateObjectInsertedOnPersistWhenShouldBeUpdatedIsTrue(): void
    {
        $entity = new ConditionalUpdateEntity(1, true);
        $persister = $this->getMockPersister($entity, 'index');
        $eventArgs = $this->createLifecycleEventArgs($entity, $this->getMockObjectManager());
        $indexable = $this->getMockIndexable('index', $entity, true);

        $listener = $this->createListener($persister, $indexable, ['indexName' => 'index']);
        $listener->postPersist($eventArgs);

        $this->assertSame($entity, \current($listener->scheduledForInsertion));

        $persister->expects($this->once())
            ->method('insertMany')
            ->with($listener->scheduledForInsertion)
        ;

        $listener->postFlush($eventArgs);
    }

    public function testConditionalUpdateObjectNotInsertedOnPersistWhenShouldBeUpdatedIsFalse(): void
    {
        $entity = new ConditionalUpdateEntity(1, false);
        $persister = $this->getMockPersister($entity, 'index');
        $eventArgs = $this->createLifecycleEventArgs($entity, $this->getMockObjectManager());
        $indexable = $this->getMockIndexable('index', $entity, true);

        $listener = $this->createListener($persister, $indexable, ['indexName' => 'index']);
        $listener->postPersist($eventArgs);

        $this->assertEmpty($listener->scheduledForInsertion);

        $persister->expects($this->never())
            ->method('insertMany')
        ;

        $listener->postFlush($eventArgs);
    }

    public function testConditionalUpdateObjectReplacedOnUpdateWhenShouldBeUpdatedIsTrue(): void
    {
        $entity = new ConditionalUpdateEntity(1, true);
        $persister = $this->getMockPersister($entity, 'index');
        $eventArgs = $this->createLifecycleEventArgs($entity, $this->getMockObjectManager());
        $indexable = $this->getMockIndexable('index', $entity, true);

        $listener = $this->createListener($persister, $indexable, ['indexName' => 'index']);
        $listener->postUpdate($eventArgs);

        $this->assertSame($entity, \current($listener->scheduledForUpdate));

        $persister->expects($this->once())
            ->method('replaceMany')
            ->with([$entity])
        ;
        $persister->expects($this->never())
            ->method('deleteById')
        ;

        $listener->postFlush($eventArgs);
    }

    public function testConditionalUpdateObjectNotReplacedOnUpdateWhenShouldBeUpdatedIsFalse(): void
    {
        $entity = new ConditionalUpdateEntity(1, false);
        $persister = $this->getMockPersister($entity, 'index');
        $eventArgs = $this->createLifecycleEventArgs($entity, $this->getMockObjectManager());
        $indexable = $this->getMockIndexable('index', $entity, true);

        $listener = $this->createListener($persister, $indexable, ['indexName' => 'index']);
        $listener->postUpdate($eventArgs);

        $this->assertEmpty($listener->scheduledForUpdate);

        $persister->expects($this->never())
            ->method('replaceMany')
        ;
        $persister->expects($this->never())
            ->method('deleteById')
        ;

        $listener->postFlush($eventArgs);
    }

    abstract protected function getLifecycleEventArgsClass(): string;

    abstract protected function getListenerClass(): string;

    abstract protected function getObjectManagerClass(): string;

    abstract protected function getClassMetadataClass(): string;

    private function createLifecycleEventArgs(): object
    {
        $refl = new \ReflectionClass($this->getLifecycleEventArgsClass());

        return $refl->newInstanceArgs(\func_get_args());
    }

    private function createListener(): object
    {
        $refl = new \ReflectionClass($this->getListenerClass());

        return $refl->newInstanceArgs(\func_get_args());
    }

    private function getMockClassMetadata(): \PHPUnit\Framework\MockObject\MockObject
    {
        return $this->createMock($this->getClassMetadataClass());
    }

    private function getMockObjectManager(): \PHPUnit\Framework\MockObject\MockObject
    {
        return $this->createMock($this->getObjectManagerClass());
    }

    private function getMockPersister(Entity $object, string $indexName): \PHPUnit\Framework\MockObject\MockObject
    {
        $mock = $this->createMock(ObjectPersister::class);

        $mock->expects($this->any())
            ->method('handlesObject')
            ->with($object)
            ->will($this->returnValue(true))
        ;

        $index = $this->createMock(Index::class);
        $index->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($indexName))
        ;

        return $mock;
    }

    private function getMockIndexable(?string $indexName, ?Entity $object = null, ?bool $return = null): \PHPUnit\Framework\MockObject\MockObject
    {
        $mock = $this->createMock(IndexableInterface::class);

        if (null !== $return) {
            $mock->expects($this->once())
                ->method('isObjectIndexable')
                ->with($indexName, $object)
                ->will($this->returnValue($return))
            ;
        }

        return $mock;
    }
}
