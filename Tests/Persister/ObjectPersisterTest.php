<?php

namespace FOQ\ElasticaBundle\Tests\ObjectPersister;

use FOQ\ElasticaBundle\Persister\ObjectPersister;
use FOQ\ElasticaBundle\Transformer\ModelToElasticaAutoTransformer;

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
       if (!class_exists('Elastica_Type')) {
           $this->markTestSkipped('The Elastica library classes are not available');
       }
    }

    public function testThatCanReplaceObject()
    {
        $modelTransformer = new  ModelToElasticaAutoTransformer();

        /** @var $typeMock \PHPUnit_Framework_MockObject_MockObject|\Elastica_Type */
        $typeMock = $this->getMockBuilder('Elastica_Type')
            ->disableOriginalConstructor()
            ->getMock();
        $typeMock->expects($this->once())
            ->method('deleteById')
            ->with($this->equalTo(123));
        $typeMock->expects($this->once())
            ->method('addDocument');

        $fields = array('name' => array());

        $objectPersister = new ObjectPersister($typeMock, $modelTransformer, 'SomeClass', $fields);
        $objectPersister->replaceOne(new POPO());
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testThatErrorIsHandledWhenCannotReplaceObject()
    {
        $modelTransformer = new  ModelToElasticaAutoTransformer();

        /** @var $typeMock \PHPUnit_Framework_MockObject_MockObject|\Elastica_Type */
        $typeMock = $this->getMockBuilder('Elastica_Type')
            ->disableOriginalConstructor()
            ->getMock();
        $typeMock->expects($this->never())
            ->method('deleteById');
        $typeMock->expects($this->never())
            ->method('addDocument');

        $fields = array('name' => array());

        $objectPersister = new InvalidObjectPersister($typeMock, $modelTransformer, 'SomeClass', $fields);
        $objectPersister->replaceOne(new POPO());
    }

    public function testThatCanInsertObject()
    {
        $modelTransformer = new  ModelToElasticaAutoTransformer();

        /** @var $typeMock \PHPUnit_Framework_MockObject_MockObject|\Elastica_Type */
        $typeMock = $this->getMockBuilder('Elastica_Type')
            ->disableOriginalConstructor()
            ->getMock();
        $typeMock->expects($this->never())
            ->method('deleteById');
        $typeMock->expects($this->once())
            ->method('addDocument');

        $fields = array('name' => array());

        $objectPersister = new ObjectPersister($typeMock, $modelTransformer, 'SomeClass', $fields);
        $objectPersister->insertOne(new POPO());
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testThatErrorIsHandledWhenCannotInsertObject()
    {
        $modelTransformer = new  ModelToElasticaAutoTransformer();

        /** @var $typeMock \PHPUnit_Framework_MockObject_MockObject|\Elastica_Type */
        $typeMock = $this->getMockBuilder('Elastica_Type')
            ->disableOriginalConstructor()
            ->getMock();
        $typeMock->expects($this->never())
            ->method('deleteById');
        $typeMock->expects($this->never())
            ->method('addDocument');

        $fields = array('name' => array());

        $objectPersister = new InvalidObjectPersister($typeMock, $modelTransformer, 'SomeClass', $fields);
        $objectPersister->insertOne(new POPO());
    }

    public function testThatCanDeleteObject()
    {
        $modelTransformer = new  ModelToElasticaAutoTransformer();

        /** @var $typeMock \PHPUnit_Framework_MockObject_MockObject|\Elastica_Type */
        $typeMock = $this->getMockBuilder('Elastica_Type')
            ->disableOriginalConstructor()
            ->getMock();
        $typeMock->expects($this->once())
            ->method('deleteById');
        $typeMock->expects($this->never())
            ->method('addDocument');

        $fields = array('name' => array());

        $objectPersister = new ObjectPersister($typeMock, $modelTransformer, 'SomeClass', $fields);
        $objectPersister->deleteOne(new POPO());
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testThatErrorIsHandledWhenCannotDeleteObject()
    {
        $modelTransformer = new  ModelToElasticaAutoTransformer();

        /** @var $typeMock \PHPUnit_Framework_MockObject_MockObject|\Elastica_Type */
        $typeMock = $this->getMockBuilder('Elastica_Type')
            ->disableOriginalConstructor()
            ->getMock();
        $typeMock->expects($this->never())
            ->method('deleteById');
        $typeMock->expects($this->never())
            ->method('addDocument');

        $fields = array('name' => array());

        $objectPersister = new InvalidObjectPersister($typeMock, $modelTransformer, 'SomeClass', $fields);
        $objectPersister->deleteOne(new POPO());
    }

    public function testThatCanInsertManyObjects()
    {
        $modelTransformer = new  ModelToElasticaAutoTransformer();

        /** @var $typeMock \PHPUnit_Framework_MockObject_MockObject|\Elastica_Type */
        $typeMock = $this->getMockBuilder('Elastica_Type')
            ->disableOriginalConstructor()
            ->getMock();
        $typeMock->expects($this->never())
            ->method('deleteById');
        $typeMock->expects($this->never())
            ->method('addDocument');
        $typeMock->expects($this->once())
            ->method('addDocuments');

        $fields = array('name' => array());

        $objectPersister = new ObjectPersister($typeMock, $modelTransformer, 'SomeClass', $fields);
        $objectPersister->insertMany(array(new POPO(), new POPO()));
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testThatErrorIsHandledWhenCannotInsertManyObject()
    {
        $modelTransformer = new ModelToElasticaAutoTransformer();

        /** @var $typeMock \PHPUnit_Framework_MockObject_MockObject|\Elastica_Type */
        $typeMock = $this->getMockBuilder('Elastica_Type')
            ->disableOriginalConstructor()
            ->getMock();
        $typeMock->expects($this->never())
            ->method('deleteById');
        $typeMock->expects($this->never())
            ->method('addDocument');
        $typeMock->expects($this->never())
            ->method('addDocuments');

        $fields = array('name' => array());

        $objectPersister = new InvalidObjectPersister($typeMock, $modelTransformer, 'SomeClass', $fields);
        $objectPersister->insertMany(array(new POPO(), new POPO()));
    }
}
