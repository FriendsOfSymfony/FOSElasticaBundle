<?php

namespace FOQ\ElasticaBundle\Tests\Doctrine\ORM;

use FOQ\ElasticaBundle\Doctrine\ORM\Listener;

class Entity
{

    public function getId()
    {
        return ListenerTest::ENTITY_ID;
    }

    public function getIdentifier()
    {
        return ListenerTest::ENTITY_IDENTIFIER;
    }

}

/**
 * @author Richard Miller <info@limethinking.co.uk>
 */
class ListenerTest extends \PHPUnit_Framework_TestCase
{

    const ENTITY_ID = 21;
    const ENTITY_IDENTIFIER = 912;

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

        $objectName = 'FOQ\ElasticaBundle\Tests\Doctrine\ORM\Entity';
        $entity     = new Entity;

        $eventArgsMock->expects($this->any())
            ->method('getEntity')
            ->will($this->returnValue($entity));

        $persisterMock->expects($this->once())
            ->method('deleteById')
            ->with($this->equalTo(self::ENTITY_ID));

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

        $objectName = 'FOQ\ElasticaBundle\Tests\Doctrine\ORM\Entity';
        $entity     = new Entity;

        $eventArgsMock->expects($this->any())
            ->method('getEntity')
            ->will($this->returnValue($entity));

        $persisterMock->expects($this->once())
            ->method('deleteById')
            ->with($this->equalTo(self::ENTITY_IDENTIFIER));

        $listener = new Listener($persisterMock, $objectName, array(), 'identifier');
        $listener->preRemove($eventArgsMock);
        $listener->postRemove($eventArgsMock);
    }

}
