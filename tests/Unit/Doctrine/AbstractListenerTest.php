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
    private $id;

    /**
     * @param int $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }
}

/**
 * See concrete MongoDB/ORM instances of this abstract test.
 *
 * @author Richard Miller <info@limethinking.co.uk>
 */
abstract class AbstractListenerTest extends TestCase
{
    public function testObjectInsertedOnPersist()
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

    public function testPersistDeferred()
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

    public function testNonIndexableObjectNotInsertedOnPersist()
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

    public function testObjectReplacedOnUpdate()
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

    public function testNonIndexableObjectRemovedOnUpdate()
    {
        $classMetadata = $this->getMockClassMetadata();
        $objectManager = $this->getMockObjectManager();

        $entity = new Entity(1);
        $persister = $this->getMockPersister($entity, 'index');
        $eventArgs = $this->createLifecycleEventArgs($entity, $objectManager);
        $indexable = $this->getMockIndexable('index', $entity, false);

        $objectManager->expects($this->any())
            ->method('getClassMetadata')
            ->with(\get_class($entity))
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

    public function testObjectDeletedOnRemove()
    {
        $classMetadata = $this->getMockClassMetadata();
        $objectManager = $this->getMockObjectManager();

        $entity = new Entity(1);
        $persister = $this->getMockPersister($entity, 'index');
        $eventArgs = $this->createLifecycleEventArgs($entity, $objectManager);
        $indexable = $this->getMockIndexable('index', $entity);

        $objectManager->expects($this->any())
            ->method('getClassMetadata')
            ->with(\get_class($entity))
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

    public function testObjectWithNonStandardIdentifierDeletedOnRemove()
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
            ->with(\get_class($entity))
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

    public function testShouldPersistOnKernelTerminateIfDeferIsTrue()
    {
        $entity = new Entity(1);
        $persister = $this->getMockPersister($entity, 'index');
        $indexable = $this->getMockIndexable(null, null, null);
        $listener = $this->createListener(
            $persister,
            $indexable,
            ['identifier' => 'identifier', 'indexName' => 'index', 'defer' => true]
        );
        $scheduledForInsertion = ['data'];
        $refListener = new \ReflectionObject($listener);
        $refScheduledForInsertion = $refListener->getProperty('scheduledForInsertion');
        $refScheduledForInsertion->setAccessible(true);
        $refScheduledForInsertion->setValue($listener, $scheduledForInsertion);
        $persister->expects($this->once())->method('insertMany')->with($scheduledForInsertion);

        $listener->onTerminate();
    }

    abstract protected function getLifecycleEventArgsClass();

    abstract protected function getListenerClass();

    /**
     * @return string
     */
    abstract protected function getObjectManagerClass();

    /**
     * @return string
     */
    abstract protected function getClassMetadataClass();

    private function createLifecycleEventArgs()
    {
        $refl = new \ReflectionClass($this->getLifecycleEventArgsClass());

        return $refl->newInstanceArgs(\func_get_args());
    }

    private function createListener()
    {
        $refl = new \ReflectionClass($this->getListenerClass());

        return $refl->newInstanceArgs(\func_get_args());
    }

    private function getMockClassMetadata()
    {
        return $this->createMock($this->getClassMetadataClass());
    }

    private function getMockObjectManager()
    {
        return $this->createMock($this->getObjectManagerClass());
    }

    private function getMockPersister(Entity $object, $indexName)
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

    private function getMockIndexable($indexName, ?Entity $object = null, $return = null)
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
