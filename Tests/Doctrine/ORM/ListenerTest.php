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

        $listener = new Listener($persisterMock, $objectName, array(), null);
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

        $listener = new Listener($persisterMock, $objectName, array(), null);
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

        $eventArgsMock->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue($entity));

        $persisterMock->expects($this->once())
            ->method('deleteOne')
            ->with($this->equalTo($entity));

        $listener = new Listener($persisterMock, $objectName, array(), null);
        $listener->postRemove($eventArgsMock);
    }

}
