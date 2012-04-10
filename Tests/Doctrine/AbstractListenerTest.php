<?php

namespace FOQ\ElasticaBundle\Tests\Doctrine;

/**
 * @author Richard Miller <info@limethinking.co.uk>
 */
abstract class AbstractListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testObjectInsertedOnPersist()
    {
        $persister = $this->getMockPersister();

        $entity = new Listener\Entity(1);
        $eventArgs = $this->createLifecycleEventArgs($entity, $this->getMockObjectManager());

        $persister->expects($this->once())
            ->method('insertOne')
            ->with($entity);

        $listener = $this->createListener($persister, get_class($entity), array());
        $listener->postPersist($eventArgs);
    }

    public function testObjectReplacedOnUpdate()
    {
        $persister = $this->getMockPersister();

        $entity = new Listener\Entity(1);
        $eventArgs = $this->createLifecycleEventArgs($entity, $this->getMockObjectManager());

        $persister->expects($this->once())
            ->method('replaceOne')
            ->with($entity);

        $listener = $this->createListener($persister, get_class($entity), array());
        $listener->postUpdate($eventArgs);
    }

    public function testObjectDeletedOnRemove()
    {
        $classMetadata = $this->getMockClassMetadata();
        $objectManager = $this->getMockObjectManager();
        $persister = $this->getMockPersister();

        $entity = new Listener\Entity(78);
        $eventArgs = $this->createLifecycleEventArgs($entity, $objectManager);

        $objectManager->expects($this->any())
            ->method('getClassMetadata')
            ->with(get_class($entity))
            ->will($this->returnValue($classMetadata));

        $classMetadata->expects($this->any())
            ->method('getFieldValue')
            ->with($entity, 'id')
            ->will($this->returnValue($entity->getId()));

        $persister->expects($this->once())
            ->method('deleteById')
            ->with($entity->getId());

        $listener = $this->createListener($persister, get_class($entity), array());
        $listener->preRemove($eventArgs);
        $listener->postRemove($eventArgs);
    }

    public function testObjectWithNonStandardIdentifierDeletedOnRemove()
    {
        $classMetadata = $this->getMockClassMetadata();
        $objectManager = $this->getMockObjectManager();
        $persister = $this->getMockPersister();

        $entity = new Listener\Entity(826);
        $eventArgs = $this->createLifecycleEventArgs($entity, $objectManager);

        $objectManager->expects($this->any())
            ->method('getClassMetadata')
            ->with(get_class($entity))
            ->will($this->returnValue($classMetadata));

        $classMetadata->expects($this->any())
            ->method('getFieldValue')
            ->with($entity, 'identifier')
            ->will($this->returnValue($entity->getId()));

        $persister->expects($this->once())
            ->method('deleteById')
            ->with($entity->getId());

        $listener = $this->createListener($persister, get_class($entity), array(), 'identifier');
        $listener->preRemove($eventArgs);
        $listener->postRemove($eventArgs);
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

    private function getMockPersister()
    {
        return $this->getMock('FOQ\ElasticaBundle\Persister\ObjectPersisterInterface');
    }
}

namespace FOQ\ElasticaBundle\Tests\Doctrine\Listener;

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
