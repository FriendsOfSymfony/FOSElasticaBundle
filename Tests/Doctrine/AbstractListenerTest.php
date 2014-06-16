<?php

namespace FOS\ElasticaBundle\Tests\Doctrine;

/**
 * See concrete MongoDB/ORM instances of this abstract test
 *
 * @author Richard Miller <info@limethinking.co.uk>
 */
abstract class ListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testObjectInsertedOnPersist()
    {
        $entity = new Listener\Entity(1);
        $persister = $this->getMockPersister($entity, 'index', 'type');
        $eventArgs = $this->createLifecycleEventArgs($entity, $this->getMockObjectManager());
        $indexable = $this->getMockIndexable('index', 'type', $entity, true);

        $listener = $this->createListener($persister, array(), $indexable);
        $listener->postPersist($eventArgs);

        $this->assertEquals($entity, current($listener->scheduledForInsertion));

        $persister->expects($this->once())
            ->method('insertMany')
            ->with($listener->scheduledForInsertion);

        $listener->postFlush($eventArgs);
    }

    public function testNonIndexableObjectNotInsertedOnPersist()
    {
        $entity = new Listener\Entity(1);
        $persister = $this->getMockPersister($entity, 'index', 'type');
        $eventArgs = $this->createLifecycleEventArgs($entity, $this->getMockObjectManager());
        $indexable = $this->getMockIndexable('index', 'type', $entity, false);

        $listener = $this->createListener($persister, array(), $indexable);
        $listener->postPersist($eventArgs);

        $this->assertEmpty($listener->scheduledForInsertion);

        $persister->expects($this->never())
            ->method('insertOne');
        $persister->expects($this->never())
            ->method('insertMany');

        $listener->postFlush($eventArgs);
    }

    public function testObjectReplacedOnUpdate()
    {
        $entity = new Listener\Entity(1);
        $persister = $this->getMockPersister($entity, 'index', 'type');
        $eventArgs = $this->createLifecycleEventArgs($entity, $this->getMockObjectManager());
        $indexable = $this->getMockIndexable('index', 'type', $entity, true);

        $listener = $this->createListener($persister, array(), $indexable);
        $listener->postUpdate($eventArgs);

        $this->assertEquals($entity, current($listener->scheduledForUpdate));

        $persister->expects($this->once())
            ->method('replaceMany')
            ->with(array($entity));
        $persister->expects($this->never())
            ->method('deleteById');

        $listener->postFlush($eventArgs);
    }

    public function testNonIndexableObjectRemovedOnUpdate()
    {
        $classMetadata = $this->getMockClassMetadata();
        $objectManager = $this->getMockObjectManager();

        $entity = new Listener\Entity(1);
        $persister = $this->getMockPersister($entity, 'index', 'type');
        $eventArgs = $this->createLifecycleEventArgs($entity, $objectManager);
        $indexable = $this->getMockIndexable('index', 'type', $entity, false);

        $objectManager->expects($this->any())
            ->method('getClassMetadata')
            ->with(get_class($entity))
            ->will($this->returnValue($classMetadata));

        $classMetadata->expects($this->any())
            ->method('getFieldValue')
            ->with($entity, 'id')
            ->will($this->returnValue($entity->getId()));

        $listener = $this->createListener($persister, array(), $indexable);
        $listener->postUpdate($eventArgs);

        $this->assertEmpty($listener->scheduledForUpdate);
        $this->assertEquals($entity->getId(), current($listener->scheduledForDeletion));

        $persister->expects($this->never())
            ->method('replaceOne');
        $persister->expects($this->once())
            ->method('deleteManyByIdentifiers')
            ->with(array($entity->getId()));

        $listener->postFlush($eventArgs);
    }

    public function testObjectDeletedOnRemove()
    {
        $classMetadata = $this->getMockClassMetadata();
        $objectManager = $this->getMockObjectManager();

        $entity = new Listener\Entity(1);
        $persister = $this->getMockPersister($entity, 'index', 'type');
        $eventArgs = $this->createLifecycleEventArgs($entity, $objectManager);
        $indexable = $this->getMockIndexable('index', 'type', $entity);

        $objectManager->expects($this->any())
            ->method('getClassMetadata')
            ->with(get_class($entity))
            ->will($this->returnValue($classMetadata));

        $classMetadata->expects($this->any())
            ->method('getFieldValue')
            ->with($entity, 'id')
            ->will($this->returnValue($entity->getId()));

        $listener = $this->createListener($persister, array(), $indexable);
        $listener->preRemove($eventArgs);

        $this->assertEquals($entity->getId(), current($listener->scheduledForDeletion));

        $persister->expects($this->once())
            ->method('deleteManyByIdentifiers')
            ->with(array($entity->getId()));

        $listener->postFlush($eventArgs);
    }

    public function testObjectWithNonStandardIdentifierDeletedOnRemove()
    {
        $classMetadata = $this->getMockClassMetadata();
        $objectManager = $this->getMockObjectManager();

        $entity = new Listener\Entity(1);
        $entity->identifier = 'foo';
        $persister = $this->getMockPersister($entity, 'index', 'type');
        $eventArgs = $this->createLifecycleEventArgs($entity, $objectManager);
        $indexable = $this->getMockIndexable('index', 'type', $entity);

        $objectManager->expects($this->any())
            ->method('getClassMetadata')
            ->with(get_class($entity))
            ->will($this->returnValue($classMetadata));

        $classMetadata->expects($this->any())
            ->method('getFieldValue')
            ->with($entity, 'identifier')
            ->will($this->returnValue($entity->getId()));

        $listener = $this->createListener($persister, array(), $indexable, 'identifier');
        $listener->preRemove($eventArgs);

        $this->assertEquals($entity->identifier, current($listener->scheduledForDeletion));

        $persister->expects($this->once())
            ->method('deleteManyByIdentifiers')
            ->with(array($entity->identifier));

        $listener->postFlush($eventArgs);
    }

    abstract protected function getLifecycleEventArgsClass();

    abstract protected function getListenerClass();

    abstract protected function getObjectManagerClass();

    abstract protected function getClassMetadataClass();

    private function createLifecycleEventArgs()
    {
        $refl = new \ReflectionClass($this->getLifecycleEventArgsClass());

        return $refl->newInstanceArgs(func_get_args());
    }

    private function createListener()
    {
        $refl = new \ReflectionClass($this->getListenerClass());

        return $refl->newInstanceArgs(func_get_args());
    }

    private function getMockClassMetadata()
    {
        return $this->getMockBuilder($this->getClassMetadataClass())
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getMockObjectManager()
    {
        return $this->getMockBuilder($this->getObjectManagerClass())
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getMockPersister($object, $indexName, $typeName)
    {
        $mock = $this->getMockBuilder('FOS\ElasticaBundle\Persister\ObjectPersister')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())
            ->method('handlesObject')
            ->with($object)
            ->will($this->returnValue(true));

        $index = $this->getMockBuilder('Elastica\Index')->disableOriginalConstructor()->getMock();
        $index->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($indexName));
        $type = $this->getMockBuilder('Elastica\Type')->disableOriginalConstructor()->getMock();
        $type->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($typeName));
        $type->expects($this->any())
            ->method('getIndex')
            ->will($this->returnValue($index));

        $mock->expects($this->any())
            ->method('getType')
            ->will($this->returnValue($type));

        return $mock;
    }

    private function getMockIndexable($indexName, $typeName, $object, $return = null)
    {
        $mock = $this->getMock('FOS\ElasticaBundle\Provider\IndexableInterface');

        if (null !== $return) {
            $mock->expects($this->once())
                ->method('isObjectIndexable')
                ->with($indexName, $typeName, $object)
                ->will($this->returnValue($return));
        }

        return $mock;
    }
}

namespace FOS\ElasticaBundle\Tests\Doctrine\Listener;

class Entity
{
    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }
}

