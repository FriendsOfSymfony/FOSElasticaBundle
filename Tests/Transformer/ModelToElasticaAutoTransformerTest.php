<?php

namespace FOQ\ElasticaBundle\Tests\Transformer\ModelToElasticaAutoTransformer;

use FOQ\ElasticaBundle\Transformer\ModelToElasticaAutoTransformer;

class POPO 
{
    public $id   = 123;
    public $name = 'someName';
    public $desc = 'desc';

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getIterator()
    {
        $iterator = new \ArrayIterator();
        $iterator->append('value1');

        return $iterator;
    }

    public function getArray()
    {
        return array('key1' => 'value1', 'key2' => 'value2');
    }
}

class ModelToElasticaAutoTransformerTest extends \PHPUnit_Framework_TestCase
{
    public function testThatCanTransformObject()
    {
        $transformer =  new ModelToElasticaAutoTransformer();
        $document    = $transformer->transform(new POPO(), array('name'));
        $data        = $document->getData();
       
        $this->assertInstanceOf('Elastica_Document', $document);
        $this->assertEquals(123, $document->getId());
        $this->assertEquals('someName', $data['name']);
    }
   
    public function testThatCanTransformObjectWithIteratorValue()
    {
        $transformer =  new ModelToElasticaAutoTransformer();
        $document    = $transformer->transform(new POPO(), array('iterator'));
        $data        = $document->getData();
     
        $this->assertEquals(array('value1'), $data['iterator']);
    }
    
    public function testThatCanTransformObjectWithArrayValue()
    {
        $transformer =  new ModelToElasticaAutoTransformer();
        $document    = $transformer->transform(new POPO(), array('array'));
        $data        = $document->getData();
     
        $this->assertEquals(array('key1' => 'value1', 'key2' => 'value2'), $data['array']);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testThatCannotTransformObjectWhenGetterDoesNotExists()
    {
        $transformer =  new ModelToElasticaAutoTransformer();
        $document    = $transformer->transform(new POPO(), array('desc'));
    }
}
