<?php

namespace FOS\ElasticaBundle\Tests\Transformer;

use Elastica\Document;
use Elastica\Result;
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
        ));
    }

    public function testGetObjectClass()
    {
        $this->collectionSetup();

        $objectClasses = $this->collection->getObjectClass();
        $this->assertEquals(array(
            'type1' => 'FOS\ElasticaBundle\Tests\Transformer\POPO',
            'type2' => 'FOS\ElasticaBundle\Tests\Transformer\POPO2',
        ), $objectClasses);
    }

    public function testTransformDelegatesToTransformers()
    {
        $this->collectionSetup();

        $document1 = new Document(123, array('data' => 'lots of data'), 'type1');
        $document2 = new Document(124, array('data' => 'not so much data'), 'type2');
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

        $document1 = new Document(123, array('data' => 'lots of data'), 'type1');
        $document2 = new Document(124, array('data' => 'not so much data'), 'type1');
        $result1 = new POPO(123, 'lots of data');
        $result2 = new POPO2(124, 'not so much data');

        $this->transformers['type1']->expects($this->once())
         ->method('transform')
         ->with(array($document1, $document2))
         ->will($this->returnValue(array($result1, $result2)));

        $results = $this->collection->transform(array($document1, $document2));

        $this->assertEquals(array(
            $result1,
            $result2,
        ), $results);
    }

    public function testTransformOrderWithIdAsObject()
    {
        $this->collectionSetup();

        $id1 = 'yo';
        $id2 = 'lo';
        $idObject1 = new IDObject($id1);
        $idObject2 = new IDObject($id2);
        $document1 = new Document($idObject1, array('data' => 'lots of data'), 'type1');
        $document2 = new Document($idObject2, array('data' => 'not so much data'), 'type1');
        $result1 = new POPO($idObject1, 'lots of data');
        $result2 = new POPO2($idObject2, 'not so much data');

        $this->transformers['type1']->expects($this->once())
         ->method('transform')
         ->with(array($document1, $document2))
         ->will($this->returnValue(array($result1, $result2)));

        $results = $this->collection->transform(array($document1, $document2));

        $this->assertEquals(array(
            $result1,
            $result2,
        ), $results);
    }

    public function testGetIdentifierFieldReturnsAMapOfIdentifiers()
    {
        $collection = new ElasticaToModelTransformerCollection(array());
        $identifiers = $collection->getIdentifierField();
        $this->assertInternalType('array', $identifiers);
        $this->assertEmpty($identifiers);

        $this->collectionSetup();
        $identifiers = $this->collection->getIdentifierField();
        $this->assertInternalType('array', $identifiers);
        $this->assertEquals(array('type1' => 'id', 'type2' => 'id'), $identifiers);
    }

    public function elasticaResults()
    {
        $result = new Result(array('_id' => 123, '_type' => 'type1'));
        $transformedObject = new POPO(123, array());

        return array(
            array(
                $result, $transformedObject,
            ),
        );
    }

    /**
     * @dataProvider elasticaResults
     */
    public function testHybridTransformDecoratesResultsWithHybridResultObjects($result, $transformedObject)
    {
        $transformer = $this->getMock('FOS\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface');
        $transformer->expects($this->any())->method('getIdentifierField')->will($this->returnValue('id'));

        $transformer
            ->expects($this->any())
            ->method('transform')
            ->will($this->returnValue(array($transformedObject)));

        $collection = new ElasticaToModelTransformerCollection(array('type1' => $transformer));

        $hybridResults = $collection->hybridTransform(array($result));

        $this->assertInternalType('array', $hybridResults);
        $this->assertNotEmpty($hybridResults);
        $this->assertContainsOnlyInstancesOf('FOS\ElasticaBundle\HybridResult', $hybridResults);

        $hybridResult = array_pop($hybridResults);
        $this->assertEquals($result, $hybridResult->getResult());
        $this->assertEquals($transformedObject, $hybridResult->getTransformed());
    }
}

class POPO
{
    public $id;
    public $data;

    /**
     * @param mixed $id
     */
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

class IDObject
{
    protected $id;

    /**
     * @param int|string $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    public function __toString()
    {
        return (string) $this->id;
    }
}
