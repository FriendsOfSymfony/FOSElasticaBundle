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
    protected function transformToElasticaDocument($object)
    {
        throw new \Exception('Invalid transformation');
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
        
        $typeMock = $this->getMockBuilder('Elastica_Type')
            ->disableOriginalConstructor()
            ->getMock();
        $typeMock->expects($this->once())
            ->method('deleteById')
            ->with($this->equalTo(123));
        $typeMock->expects($this->once())
            ->method('addDocument');
        
        $mappingMock = $this->getMockBuilder('FOQ\ElasticaBundle\MappingRegistry')
            ->disableOriginalConstructor()
            ->getMock();
        $mappingMock->expects($this->once())
            ->method('getTypeFieldNames')
            ->will($this->returnValue(array('name')));

        $objectPersister = new ObjectPersister($typeMock, $modelTransformer, 'SomeClass', $mappingMock); 
        $objectPersister->replaceOne(new POPO());
    }
   
    /**
     * @expectedException Exception
     */ 
    public function testThatErrorIsHandledWhenCannotReplaceObject()
    {
        $modelTransformer = new  ModelToElasticaAutoTransformer();
        
        $mappingMock = $this->getMockBuilder('FOQ\ElasticaBundle\MappingRegistry')
            ->disableOriginalConstructor()
            ->getMock();
        
        $typeMock = $this->getMockBuilder('Elastica_Type')
            ->disableOriginalConstructor()
            ->getMock();
        $typeMock->expects($this->never())
            ->method('deleteById');
        $typeMock->expects($this->never())
            ->method('addDocument');
         
        $objectPersister = new InvalidObjectPersister($typeMock, $modelTransformer, 'SomeClass', $mappingMock); 
        $objectPersister->replaceOne(new POPO());
    }
    
    public function testThatCanInsertObject()
    {
        $modelTransformer = new  ModelToElasticaAutoTransformer();
        
        $typeMock = $this->getMockBuilder('Elastica_Type')
            ->disableOriginalConstructor()
            ->getMock();
        $typeMock->expects($this->never())
            ->method('deleteById');
        $typeMock->expects($this->once())
            ->method('addDocument');
        
        $mappingMock = $this->getMockBuilder('FOQ\ElasticaBundle\MappingRegistry')
            ->disableOriginalConstructor()
            ->getMock();
        $mappingMock->expects($this->once())
            ->method('getTypeFieldNames')
            ->will($this->returnValue(array('name')));

        $objectPersister = new ObjectPersister($typeMock, $modelTransformer, 'SomeClass', $mappingMock); 
        $objectPersister->insertOne(new POPO());
    }
    
    /**
     * @expectedException Exception
     */ 
    public function testThatErrorIsHandledWhenCannotInsertObject()
    {
        $modelTransformer = new  ModelToElasticaAutoTransformer();
        
        $typeMock = $this->getMockBuilder('Elastica_Type')
            ->disableOriginalConstructor()
            ->getMock();
        $typeMock->expects($this->never())
            ->method('deleteById');
        $typeMock->expects($this->never())
            ->method('addDocument');
        
        $objectPersister = new InvalidObjectPersister($typeMock, $modelTransformer, 'SomeClass', $mappingMock);
        $objectPersister->insertOne(new POPO());
    }
    
    public function testThatCanDeleteObject()
    {
        $modelTransformer = new  ModelToElasticaAutoTransformer();
        
        $typeMock = $this->getMockBuilder('Elastica_Type')
            ->disableOriginalConstructor()
            ->getMock();
        $typeMock->expects($this->once())
            ->method('deleteById');
        $typeMock->expects($this->never())
            ->method('addDocument');
        
        $mappingMock = $this->getMockBuilder('FOQ\ElasticaBundle\MappingRegistry')
            ->disableOriginalConstructor()
            ->getMock();
        $mappingMock->expects($this->any())
            ->method('getTypeFieldNames')
            ->will($this->returnValue(array('name')));

        $objectPersister = new ObjectPersister($typeMock, $modelTransformer, 'SomeClass', $mappingMock); 
        $objectPersister->deleteOne(new POPO());
    }
    
    /**
     * @expectedException Exception
     */ 
    public function testThatErrorIsHandledWhenCannotDeleteObject()
    {
        $modelTransformer = new  ModelToElasticaAutoTransformer();
        
        $typeMock = $this->getMockBuilder('Elastica_Type')
            ->disableOriginalConstructor()
            ->getMock();
        $typeMock->expects($this->never())
            ->method('deleteById');
        $typeMock->expects($this->never())
            ->method('addDocument');
        
        $mappingMock = $this->getMockBuilder('FOQ\ElasticaBundle\MappingRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $objectPersister = new InvalidObjectPersister($typeMock, $modelTransformer, 'SomeClass', $mappingMock); 
        $objectPersister->deleteOne(new POPO());
    }
    
    public function testThatCanInsertManyObjects()
    {
        $modelTransformer = new  ModelToElasticaAutoTransformer();
        
        $typeMock = $this->getMockBuilder('Elastica_Type')
            ->disableOriginalConstructor()
            ->getMock();
        $typeMock->expects($this->never())
            ->method('deleteById');
        $typeMock->expects($this->never())
            ->method('addDocument');
        $typeMock->expects($this->once())
            ->method('addDocuments');
        
        $mappingMock = $this->getMockBuilder('FOQ\ElasticaBundle\MappingRegistry')
            ->disableOriginalConstructor()
            ->getMock();
        $mappingMock->expects($this->any())
            ->method('getTypeFieldNames')
            ->will($this->returnValue(array('name')));

        $objectPersister = new ObjectPersister($typeMock, $modelTransformer, 'SomeClass', $mappingMock); 
        $objectPersister->insertMany(array(new POPO(), new POPO()));
    }
    
    /**
     * @expectedException Exception
     */ 
    public function testThatErrorIsHandledWhenCannotInsertManyObject()
    {
        $modelTransformer = new ModelToElasticaAutoTransformer();
        
        $typeMock = $this->getMockBuilder('Elastica_Type')
            ->disableOriginalConstructor()
            ->getMock();
        $typeMock->expects($this->never())
            ->method('deleteById');
        $typeMock->expects($this->never())
            ->method('addDocument');
        $typeMock->expects($this->never())
            ->method('addDocuments');
        
        $mappingMock = $this->getMockBuilder('FOQ\ElasticaBundle\MappingRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $objectPersister = new InvalidObjectPersister($typeMock, $modelTransformer, 'SomeClass', $mappingMock); 
        $objectPersister->insertMany(array(new POPO(), new POPO()));
    }
}
