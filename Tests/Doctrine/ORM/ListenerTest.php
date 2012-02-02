<?php

namespace FOQ\ElasticaBundle\Tests\Doctrine\ORM;

use FOQ\ElasticaBundle\Doctrine\ORM\Listener;

class Entity{}

/**
 * @author Richard Miller <info@limethinking.co.uk>
 */
class ListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testObjectInsertedOnPersist()
    {
        $persisterMock = $this->getMockBuilder('FOQ\ElasticaBundle\Persister\ObjectPersisterInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $eventArgsMock = $this->getMockBuilder('Doctrine\ORM\Event\LifecycleEventArgs')
            ->disableOriginalConstructor()
            ->getMock();

        $objectName = 'FOQ\ElasticaBundle\Tests\Doctrine\ORM\Entity';
        $entity = new Entity;

        $eventArgsMock->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue($entity));

        $persisterMock->expects($this->once())
            ->method('insertOne')
            ->with($this->equalTo($entity));

        $listener = new Listener($persisterMock, $objectName, array());
        $listener->postPersist($eventArgsMock);
    }

    public function testObjectReplacedOnUpdate()
    {
        $persisterMock = $this->getMockBuilder('FOQ\ElasticaBundle\Persister\ObjectPersisterInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $eventArgsMock = $this->getMockBuilder('Doctrine\ORM\Event\LifecycleEventArgs')
            ->disableOriginalConstructor()
            ->getMock();

        $objectName = 'FOQ\ElasticaBundle\Tests\Doctrine\ORM\Entity';
        $entity     = new Entity;

        $eventArgsMock->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue($entity));

        $persisterMock->expects($this->once())
            ->method('replaceOne')
            ->with($this->equalTo($entity));

        $listener = new Listener($persisterMock, $objectName, array());
        $listener->postUpdate($eventArgsMock);
    }

    public function testObjectDeletedOnRemove()
    {
        $persisterMock = $this->getMockBuilder('FOQ\ElasticaBundle\Persister\ObjectPersisterInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $eventArgsMock = $this->getMockBuilder('Doctrine\ORM\Event\LifecycleEventArgs')
            ->disableOriginalConstructor()
            ->getMock();

        $entityManagerMock = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $metadataMock = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $objectName = 'FOQ\ElasticaBundle\Tests\Doctrine\ORM\Entity';
        $entity     = new Entity;
        $entityId   = 21;

        $eventArgsMock->expects($this->any())
            ->method('getEntity')
            ->will($this->returnValue($entity));

        $eventArgsMock->expects($this->any())
            ->method('getEntityManager')
            ->will($this->returnValue($entityManagerMock));

        $entityManagerMock->expects($this->any())
            ->method('getClassMetadata')
            ->will($this->returnValue($metadataMock));

        $metadataMock->expects($this->any())
            ->method('getFieldValue')
            ->with($this->equalTo($entity), $this->equalTo('id'))
            ->will($this->returnValue($entityId));

        $persisterMock->expects($this->once())
            ->method('deleteById')
            ->with($this->equalTo($entityId));

        $listener = new Listener($persisterMock, $objectName, array());
        $listener->preRemove($eventArgsMock);
        $listener->postRemove($eventArgsMock);
    }

    public function testObjectWithNonStandardIdentifierDeletedOnRemove()
    {
        $persisterMock = $this->getMockBuilder('FOQ\ElasticaBundle\Persister\ObjectPersisterInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $eventArgsMock = $this->getMockBuilder('Doctrine\ORM\Event\LifecycleEventArgs')
            ->disableOriginalConstructor()
            ->getMock();

        $entityManagerMock = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $metadataMock = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $objectName       = 'FOQ\ElasticaBundle\Tests\Doctrine\ORM\Entity';
        $entity           = new Entity;
        $entityIdentifier = 924;
        $identifierField  = 'identifier';

        $eventArgsMock->expects($this->any())
            ->method('getEntity')
            ->will($this->returnValue($entity));

        $eventArgsMock->expects($this->any())
            ->method('getEntityManager')
            ->will($this->returnValue($entityManagerMock));

        $entityManagerMock->expects($this->any())
            ->method('getClassMetadata')
            ->will($this->returnValue($metadataMock));

        $metadataMock->expects($this->any())
            ->method('getFieldValue')
            ->with($this->equalTo($entity), $this->equalTo($identifierField))
            ->will($this->returnValue($entityIdentifier));

        $persisterMock->expects($this->once())
            ->method('deleteById')
            ->with($this->equalTo($entityIdentifier));

        $listener = new Listener($persisterMock, $objectName, array(), $identifierField);
        $listener->preRemove($eventArgsMock);
        $listener->postRemove($eventArgsMock);
    }
}
