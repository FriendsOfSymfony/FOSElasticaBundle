<?php

namespace FOS\ElasticaBundle\Tests\ObjectPersister;

use FOS\ElasticaBundle\Persister\ObjectPersister;
use FOS\ElasticaBundle\Transformer\ModelToElasticaAutoTransformer;
use Symfony\Component\PropertyAccess\PropertyAccess;

class POPO
{
    public $id   = 123;

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return 'popoName';
    }
}

class InvalidObjectPersister extends ObjectPersister
{
    public function transformToElasticaDocument($object)
    {
        throw new \BadMethodCallException('Invalid transformation');
    }
}

class ObjectPersisterTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
       if (!class_exists('Elastica\Type')) {
           $this->markTestSkipped('The Elastica library classes are not available');
       }
    }

    public function testThatCanReplaceObject()
    {
        $transformer = $this->getTransformer();

        /** @var $typeMock \PHPUnit_Framework_MockObject_MockObject|\Elastica\Type */
        $typeMock = $this->getMockBuilder('Elastica\Type')
            ->disableOriginalConstructor()
            ->getMock();
        $typeMock->expects($this->once())
            ->method('deleteById')
            ->with($this->equalTo(123));
        $typeMock->expects($this->once())
            ->method('addDocument');

        $fields = array('name' => array());

        $objectPersister = new ObjectPersister($typeMock, $transformer, 'SomeClass', $fields);
        $objectPersister->replaceOne(new POPO());
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testThatErrorIsHandledWhenCannotReplaceObject()
    {
        $transformer = $this->getTransformer();

        /** @var $typeMock \PHPUnit_Framework_MockObject_MockObject|\Elastica\Type */
        $typeMock = $this->getMockBuilder('Elastica\Type')
            ->disableOriginalConstructor()
            ->getMock();
        $typeMock->expects($this->never())
            ->method('deleteById');
        $typeMock->expects($this->never())
            ->method('addDocument');

        $fields = array('name' => array());

        $objectPersister = new InvalidObjectPersister($typeMock, $transformer, 'SomeClass', $fields);
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
            ->method('addDocument');

        $fields = array('name' => array());

        $objectPersister = new ObjectPersister($typeMock, $transformer, 'SomeClass', $fields);
        $objectPersister->insertOne(new POPO());
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testThatErrorIsHandledWhenCannotInsertObject()
    {
        $transformer = $this->getTransformer();

        /** @var $typeMock \PHPUnit_Framework_MockObject_MockObject|\Elastica\Type */
        $typeMock = $this->getMockBuilder('Elastica\Type')
            ->disableOriginalConstructor()
            ->getMock();
        $typeMock->expects($this->never())
            ->method('deleteById');
        $typeMock->expects($this->never())
            ->method('addDocument');

        $fields = array('name' => array());

        $objectPersister = new InvalidObjectPersister($typeMock, $transformer, 'SomeClass', $fields);
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
            ->method('deleteById');
        $typeMock->expects($this->never())
            ->method('addDocument');

        $fields = array('name' => array());

        $objectPersister = new ObjectPersister($typeMock, $transformer, 'SomeClass', $fields);
        $objectPersister->deleteOne(new POPO());
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testThatErrorIsHandledWhenCannotDeleteObject()
    {
        $transformer = $this->getTransformer();

        /** @var $typeMock \PHPUnit_Framework_MockObject_MockObject|\Elastica\Type */
        $typeMock = $this->getMockBuilder('Elastica\Type')
            ->disableOriginalConstructor()
            ->getMock();
        $typeMock->expects($this->never())
            ->method('deleteById');
        $typeMock->expects($this->never())
            ->method('addDocument');

        $fields = array('name' => array());

        $objectPersister = new InvalidObjectPersister($typeMock, $transformer, 'SomeClass', $fields);
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
            ->method('addDocument');
        $typeMock->expects($this->once())
            ->method('addDocuments');

        $fields = array('name' => array());

        $objectPersister = new ObjectPersister($typeMock, $transformer, 'SomeClass', $fields);
        $objectPersister->insertMany(array(new POPO(), new POPO()));
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testThatErrorIsHandledWhenCannotInsertManyObject()
    {
        $transformer = $this->getTransformer();

        /** @var $typeMock \PHPUnit_Framework_MockObject_MockObject|\Elastica\Type */
        $typeMock = $this->getMockBuilder('Elastica\Type')
            ->disableOriginalConstructor()
            ->getMock();
        $typeMock->expects($this->never())
            ->method('deleteById');
        $typeMock->expects($this->never())
            ->method('addDocument');
        $typeMock->expects($this->never())
            ->method('addDocuments');

        $fields = array('name' => array());

        $objectPersister = new InvalidObjectPersister($typeMock, $transformer, 'SomeClass', $fields);
        $objectPersister->insertMany(array(new POPO(), new POPO()));
    }

    /**
     * @return ModelToElasticaAutoTransformer
     */
    private function getTransformer()
    {
        $transformer = new ModelToElasticaAutoTransformer();
        $transformer->setPropertyAccessor(PropertyAccess::getPropertyAccessor());

        return $transformer;
    }
}
