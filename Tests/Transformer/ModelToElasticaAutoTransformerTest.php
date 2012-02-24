<?php

namespace FOQ\ElasticaBundle\Tests\Transformer\ModelToElasticaAutoTransformer;

use FOQ\ElasticaBundle\Transformer\ModelToElasticaAutoTransformer;

class POPO
{
    public $id        = 123;
    public $name      = 'someName';
    public $desc      = 'desc';
    public $float     = 7.2;
    public $bool      = true;
    public $falseBool = false;
    public $date;
    public $nullValue;

    public function __construct()
    {
        $this->date = new \DateTime('1979-05-05');
    }

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

    public function getMultiArray()
    {
        return array(
            'key1'  => 'value1',
            'key2'  => array('value2', false, 123, 8.9, new \DateTime('1978-09-07')),
        );
    }

    public function getBool()
    {
        return $this->bool;
    }

    public function getFalseBool()
    {
        return $this->falseBool;
    }

    public function getFloat()
    {
        return $this->float;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getNullValue()
    {
        return $this->nullValue;
    }

}

class ModelToElasticaAutoTransformerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
       if (!class_exists('Elastica_Document')) {;
           $this->markTestSkipped('The Elastica library classes are not available');
       }
    }

    public function testThatCanTransformObject()
    {
        $transformer =  new ModelToElasticaAutoTransformer();
        $document    = $transformer->transform(new POPO(), array('name'));
        $data        = $document->getData();

        $this->assertInstanceOf('Elastica_Document', $document);
        $this->assertEquals(123, $document->getId());
        $this->assertEquals('someName', $data['name']);
    }

    public function testThatCanTransformObjectWithCorrectTypes()
    {
        $transformer = new ModelToElasticaAutoTransformer();
        $document    = $transformer->transform(new POPO(), array('name', 'float', 'bool', 'date', 'falseBool'));
        $data        = $document->getData();

        $this->assertInstanceOf('Elastica_Document', $document);
        $this->assertEquals(123, $document->getId());
        $this->assertEquals('someName', $data['name']);
        $this->assertEquals(7.2, $data['float']);
        $this->assertEquals(true, $data['bool']);
        $this->assertEquals(false, $data['falseBool']);
        $expectedDate = new \DateTime('1979-05-05');
        $this->assertEquals($expectedDate->format('U'), $data['date']);
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

    public function testThatCanTransformObjectWithMultiDimensionalArrayValue()
    {
        $transformer = new ModelToElasticaAutoTransformer();
        $document    = $transformer->transform(new POPO(), array('multiArray'));
        $data        = $document->getData();

        $expectedDate = new \DateTime('1978-09-07');

        $this->assertEquals(
            array(
                 'key1'  => 'value1',
                 'key2'  => array('value2', false, 123, 8.9, $expectedDate->format('U')),
            ), $data['multiArray']
        );
    }

    public function testThatNullValuesAreFilteredOut()
    {
        $transformer = new ModelToElasticaAutoTransformer();
        $document    = $transformer->transform(new POPO(), array('nullValue'));
        $data        = $document->getData();

        $this->assertInstanceOf('Elastica_Document', $document);
        $this->assertEquals(123, $document->getId());
        $this->assertFalse(array_key_exists('nullValue', $data));
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
