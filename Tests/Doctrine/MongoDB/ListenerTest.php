<?php

namespace FOQ\ElasticaBundle\Tests\Doctrine\MongoDB;

use FOQ\ElasticaBundle\Doctrine\MongoDB\Listener;

class Document{}

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

        $eventArgsMock = $this->getMockBuilder('Doctrine\ODM\MongoDB\Event\LifecycleEventArgs')
            ->disableOriginalConstructor()
            ->getMock();

        $objectName = 'FOQ\ElasticaBundle\Tests\Doctrine\MongoDB\Document';
        $document = new Document();

        $eventArgsMock->expects($this->once())
            ->method('getDocument')
            ->will($this->returnValue($document));

        $persisterMock->expects($this->once())
            ->method('insertOne')
            ->with($this->equalTo($document));

        $listener = new Listener($persisterMock, $objectName, array(), null);
        $listener->postPersist($eventArgsMock);
    }

    public function testObjectReplacedOnUpdate()
    {
        $persisterMock = $this->getMockBuilder('FOQ\ElasticaBundle\Persister\ObjectPersisterInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $eventArgsMock = $this->getMockBuilder('Doctrine\ODM\MongoDB\Event\LifecycleEventArgs')
            ->disableOriginalConstructor()
            ->getMock();

        $objectName = 'FOQ\ElasticaBundle\Tests\Doctrine\MongoDB\Document';
        $document = new Document();

        $eventArgsMock->expects($this->once())
            ->method('getDocument')
            ->will($this->returnValue($document));

        $persisterMock->expects($this->once())
            ->method('replaceOne')
            ->with($this->equalTo($document));

        $listener = new Listener($persisterMock, $objectName, array(), null);
        $listener->postUpdate($eventArgsMock);
    }

    public function testObjectDeletedOnRemove()
    {
        $persisterMock = $this->getMockBuilder('FOQ\ElasticaBundle\Persister\ObjectPersisterInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $eventArgsMock = $this->getMockBuilder('Doctrine\ODM\MongoDB\Event\LifecycleEventArgs')
            ->disableOriginalConstructor()
            ->getMock();

        $objectName = 'FOQ\ElasticaBundle\Tests\Doctrine\MongoDB\Document';
        $document     = new Document();

        $eventArgsMock->expects($this->once())
            ->method('getDocument')
            ->will($this->returnValue($document));

        $persisterMock->expects($this->once())
            ->method('deleteOne')
            ->with($this->equalTo($document));

        $listener = new Listener($persisterMock, $objectName, array(), null);
        $listener->postRemove($eventArgsMock);
    }

}
