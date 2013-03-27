<?php

namespace FOS\ElasticaBundle\Tests\Transformer;

use FOS\ElasticaBundle\Transformer\ElasticaToModelTransformerCollection;

class ElasticaToModelTransformerCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \FOS\ElasticaBundle\Transformer\ElasticaToModelTransformerCollection
     */
    protected $collection;
    protected $transformers = array();

    protected function collectionSetup()
    {
        $transformer1 = $this->getMock('FOS\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface');
        $transformer1->expects($this->any())
            ->method('getObjectClass')
            ->will($this->returnValue('FOS\ElasticaBundle\Tests\Transformer\POPO'));

        $transformer1->expects($this->any())
            ->method('getIdentifierField')
            ->will($this->returnValue('id'));

        $transformer2 = $this->getMock('FOS\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface');
        $transformer2->expects($this->any())
            ->method('getObjectClass')
            ->will($this->returnValue('FOS\ElasticaBundle\Tests\Transformer\POPO2'));

        $transformer2->expects($this->any())
            ->method('getIdentifierField')
            ->will($this->returnValue('id'));

        $this->collection = new ElasticaToModelTransformerCollection($this->transformers = array(
            'type1' => $transformer1,
            'type2' => $transformer2,
        ), array());
    }

    public function testGetObjectClass()
    {
        $this->collectionSetup();

        $objectClasses = $this->collection->getObjectClass();
        $this->assertEquals(array(
            'type1' => 'FOS\ElasticaBundle\Tests\Transformer\POPO',
            'type2' => 'FOS\ElasticaBundle\Tests\Transformer\POPO2'
        ), $objectClasses);
    }

    public function testTransformDelegatesToTransformers()
    {
        $this->collectionSetup();

        $document1 = new \Elastica_Document(123, array('data' => 'lots of data'), 'type1');
        $document2 = new \Elastica_Document(124, array('data' => 'not so much data'), 'type2');
        $result1 = new POPO(123, 'lots of data');
        $result2 = new POPO2(124, 'not so much data');

        $this->transformers['type1']->expects($this->once())
            ->method('transform')
            ->with(array($document1))
            ->will($this->returnValue(array($result1)));

        $this->transformers['type2']->expects($this->once())
            ->method('transform')
            ->with(array($document2))
            ->will($this->returnValue(array($result2)));

        $results = $this->collection->transform(array($document1, $document2));

        $this->assertEquals(array(
            $result1,
            $result2,
        ), $results);
    }

    public function testTransformOrder()
    {
        $this->collectionSetup();

        $document1 = new \Elastica_Document(123, array('data' => 'lots of data'), 'type1');
        $document2 = new \Elastica_Document(124, array('data' => 'not so much data'), 'type1');
        $result1 = new POPO(123, 'lots of data');
        $result2 = new POPO2(124, 'not so much data');

        $this->transformers['type1']->expects($this->once())
         ->method('transform')
         ->with(array($document1,$document2))
         ->will($this->returnValue(array($result1,$result2)));

        $results = $this->collection->transform(array($document1, $document2));

        $this->assertEquals(array(
            $result1,
            $result2,
        ), $results);
    }
}

class POPO
{
    public $id;
    public $data;

    public function __construct($id, $data)
    {
        $this->data = $data;
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }
}

class POPO2 extends POPO
{

}
