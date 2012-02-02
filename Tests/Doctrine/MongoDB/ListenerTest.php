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

        $documentManagerMock = $this->getMockBuilder('Doctrine\ODM\MongoDB\DocumentManager')
            ->disableOriginalConstructor()
            ->getMock();

        $metadataMock = $this->getMockBuilder('Doctrine\ODM\MongoDB\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $objectName = 'FOQ\ElasticaBundle\Tests\Doctrine\MongoDB\Document';
        $document   = new Document();
        $documentId = 78;

        $eventArgsMock->expects($this->any())
            ->method('getDocument')
            ->will($this->returnValue($document));

        $eventArgsMock->expects($this->any())
            ->method('getDocumentManager')
            ->will($this->returnValue($documentManagerMock));

        $documentManagerMock->expects($this->any())
            ->method('getClassMetadata')
            ->will($this->returnValue($metadataMock));

        $metadataMock->expects($this->any())
            ->method('getFieldValue')
            ->with($this->equalTo($document), $this->equalTo('id'))
            ->will($this->returnValue($documentId));

        $persisterMock->expects($this->once())
            ->method('deleteById')
            ->with($this->equalTo($documentId));

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

        $documentManagerMock = $this->getMockBuilder('Doctrine\ODM\MongoDB\DocumentManager')
            ->disableOriginalConstructor()
            ->getMock();

        $metadataMock = $this->getMockBuilder('Doctrine\ODM\MongoDB\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $objectName = 'FOQ\ElasticaBundle\Tests\Doctrine\MongoDB\Document';
        $document   = new Document();
        $documentIdentifier = 826;
        $identifierField = 'identifier';

        $eventArgsMock->expects($this->any())
            ->method('getDocument')
            ->will($this->returnValue($document));

        $eventArgsMock->expects($this->any())
            ->method('getDocumentManager')
            ->will($this->returnValue($documentManagerMock));

        $documentManagerMock->expects($this->any())
            ->method('getClassMetadata')
            ->will($this->returnValue($metadataMock));

        $metadataMock->expects($this->any())
            ->method('getFieldValue')
            ->with($this->equalTo($document), $this->equalTo($identifierField))
            ->will($this->returnValue($documentIdentifier));

        $persisterMock->expects($this->once())
            ->method('deleteById')
            ->with($this->equalTo($documentIdentifier));

        $listener = new Listener($persisterMock, $objectName, array(), 'identifier');
        $listener->preRemove($eventArgsMock);
        $listener->postRemove($eventArgsMock);
    }
}
