<?php

namespace FOQ\ElasticaBundle\Tests\Doctrine\MongoDB;

use FOQ\ElasticaBundle\Doctrine\MongoDB\Listener;

class Document
{
    public function getId()
    {
        return ListenerTest::DOCUMENT_ID;
    }

    public function getIdentifier()
    {
        return ListenerTest::DOCUMENT_IDENTIFIER;
    }
}

/**
 * @author Richard Miller <info@limethinking.co.uk>
 */
class ListenerTest extends \PHPUnit_Framework_TestCase
{
    const DOCUMENT_ID = 78;
    const DOCUMENT_IDENTIFIER = 826;

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

        $listener = new Listener($persisterMock, $objectName, array());
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

        $listener = new Listener($persisterMock, $objectName, array());
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

        $eventArgsMock->expects($this->any())
            ->method('getDocument')
            ->will($this->returnValue($document));

        $persisterMock->expects($this->once())
            ->method('deleteById')
            ->with($this->equalTo(self::DOCUMENT_ID));

        $listener = new Listener($persisterMock, $objectName, array());
        $listener->preRemove($eventArgsMock);
        $listener->postRemove($eventArgsMock);
    }

    public function testObjectWithNonStandardIdentifierDeletedOnRemove()
    {
        $persisterMock = $this->getMockBuilder('FOQ\ElasticaBundle\Persister\ObjectPersisterInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $eventArgsMock = $this->getMockBuilder('Doctrine\ODM\MongoDB\Event\LifecycleEventArgs')
            ->disableOriginalConstructor()
            ->getMock();

        $objectName = 'FOQ\ElasticaBundle\Tests\Doctrine\MongoDB\Document';
        $document   = new Document();

        $eventArgsMock->expects($this->any())
            ->method('getDocument')
            ->will($this->returnValue($document));

        $persisterMock->expects($this->once())
            ->method('deleteById')
            ->with($this->equalTo(self::DOCUMENT_IDENTIFIER));

        $listener = new Listener($persisterMock, $objectName, array(), 'identifier');
        $listener->preRemove($eventArgsMock);
        $listener->postRemove($eventArgsMock);
    }
}
