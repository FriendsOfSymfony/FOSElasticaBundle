<?php

namespace FOS\ElasticaBundle\Tests\ObjectSerializerPersister;

use FOS\ElasticaBundle\Persister\ObjectSerializerPersister;
use FOS\ElasticaBundle\Transformer\ModelToElasticaIdentifierTransformer;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\PropertyAccess\PropertyAccess;

class POPO
{
    public $id   = 123;
    public $name = 'popoName';

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }
}

class ObjectSerializerPersisterTest extends \PHPUnit_Framework_TestCase
{
    public function testThatCanReplaceObject()
    {
        $transformer = $this->getTransformer();

        /** @var $typeMock \PHPUnit_Framework_MockObject_MockObject|\Elastica\Type */
        $typeMock = $this->getMockBuilder('Elastica\Type')
            ->disableOriginalConstructor()
            ->getMock();
        $typeMock->expects($this->once())
            ->method('updateDocuments');

        $serializerMock = $this->getMockBuilder('FOS\ElasticaBundle\Serializer\Callback')->getMock();
        $serializerMock->expects($this->once())->method('serialize');

        $dispatcherMock = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcher')->getMock();

        $objectPersister = new ObjectSerializerPersister($typeMock, $transformer, 'SomeClass', array($serializerMock, 'serialize'), $dispatcherMock);
        $objectPersister->replaceOne(new POPO());
    }

    public function testThatCanInsertObject()
    {
        $transformer = $this->getTransformer();

        /** @var $typeMock \PHPUnit_Framework_MockObject_MockObject|\Elastica\Type */
        $typeMock = $this->getMockBuilder('Elastica\Type')
            ->disableOriginalConstructor()
            ->getMock();
        $typeMock->expects($this->never())
            ->method('deleteById');
        $typeMock->expects($this->once())
            ->method('addDocuments');

        $serializerMock = $this->getMockBuilder('FOS\ElasticaBundle\Serializer\Callback')->getMock();
        $serializerMock->expects($this->once())->method('serialize');

        $dispatcherMock = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcher')->getMock();

        $objectPersister = new ObjectSerializerPersister($typeMock, $transformer, 'SomeClass', array($serializerMock, 'serialize'), $dispatcherMock);
        $objectPersister->insertOne(new POPO());
    }

    public function testThatCanDeleteObject()
    {
        $transformer = $this->getTransformer();

        /** @var $typeMock \PHPUnit_Framework_MockObject_MockObject|\Elastica\Type */
        $typeMock = $this->getMockBuilder('Elastica\Type')
            ->disableOriginalConstructor()
            ->getMock();
        $typeMock->expects($this->once())
            ->method('deleteDocuments');
        $typeMock->expects($this->never())
            ->method('addDocument');

        $serializerMock = $this->getMockBuilder('FOS\ElasticaBundle\Serializer\Callback')->getMock();
        $serializerMock->expects($this->once())->method('serialize');

        $dispatcherMock = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcher')->getMock();

        $objectPersister = new ObjectSerializerPersister($typeMock, $transformer, 'SomeClass', array($serializerMock, 'serialize'), $dispatcherMock);
        $objectPersister->deleteOne(new POPO());
    }

    public function testThatCanInsertManyObjects()
    {
        $transformer = $this->getTransformer();

        /** @var $typeMock \PHPUnit_Framework_MockObject_MockObject|\Elastica\Type */
        $typeMock = $this->getMockBuilder('Elastica\Type')
            ->disableOriginalConstructor()
            ->getMock();
        $typeMock->expects($this->never())
            ->method('deleteById');
        $typeMock->expects($this->never())
            ->method('addObject');
        $typeMock->expects($this->never())
            ->method('addObjects');
        $typeMock->expects($this->once())
            ->method('addDocuments');

        $serializerMock = $this->getMockBuilder('FOS\ElasticaBundle\Serializer\Callback')->getMock();
        $serializerMock->expects($this->exactly(2))->method('serialize');

        $dispatcherMock = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcher')->getMock();

        $objectPersister = new ObjectSerializerPersister($typeMock, $transformer, 'SomeClass', array($serializerMock, 'serialize'), $dispatcherMock);
        $objectPersister->insertMany(array(new POPO(), new POPO()));
    }

    /**
     * @return ModelToElasticaIdentifierTransformer
     */
    private function getTransformer()
    {
        $transformer = new ModelToElasticaIdentifierTransformer();
        $transformer->setPropertyAccessor(PropertyAccess::createPropertyAccessor());

        return $transformer;
    }
}
