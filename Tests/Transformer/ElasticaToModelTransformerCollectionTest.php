<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
    protected $transformers = [];

    public function testGetObjectClass()
    {
        $this->collectionSetup();

        $objectClasses = $this->collection->getObjectClass();
        $this->assertSame([
            'type1' => 'FOS\ElasticaBundle\Tests\Transformer\POPO',
            'type2' => 'FOS\ElasticaBundle\Tests\Transformer\POPO2',
        ], $objectClasses);
    }

    public function testTransformDelegatesToTransformers()
    {
        $this->collectionSetup();

        $document1 = new Document(123, ['data' => 'lots of data'], 'type1');
        $document2 = new Document(124, ['data' => 'not so much data'], 'type2');
        $result1 = new POPO(123, 'lots of data');
        $result2 = new POPO2(124, 'not so much data');

        $this->transformers['type1']->expects($this->once())
            ->method('transform')
            ->with([$document1])
            ->will($this->returnValue([$result1]));

        $this->transformers['type2']->expects($this->once())
            ->method('transform')
            ->with([$document2])
            ->will($this->returnValue([$result2]));

        $results = $this->collection->transform([$document1, $document2]);

        $this->assertSame([
            $result1,
            $result2,
        ], $results);
    }

    public function testTransformOrder()
    {
        $this->collectionSetup();

        $document1 = new Document(123, ['data' => 'lots of data'], 'type1');
        $document2 = new Document(124, ['data' => 'not so much data'], 'type1');
        $result1 = new POPO(123, 'lots of data');
        $result2 = new POPO2(124, 'not so much data');

        $this->transformers['type1']->expects($this->once())
         ->method('transform')
         ->with([$document1, $document2])
         ->will($this->returnValue([$result1, $result2]));

        $results = $this->collection->transform([$document1, $document2]);

        $this->assertSame([
            $result1,
            $result2,
        ], $results);
    }

    public function testTransformOrderWithIdAsObject()
    {
        $this->collectionSetup();

        $id1 = 'yo';
        $id2 = 'lo';
        $idObject1 = new IDObject($id1);
        $idObject2 = new IDObject($id2);
        $document1 = new Document($idObject1, ['data' => 'lots of data'], 'type1');
        $document2 = new Document($idObject2, ['data' => 'not so much data'], 'type1');
        $result1 = new POPO($idObject1, 'lots of data');
        $result2 = new POPO2($idObject2, 'not so much data');

        $this->transformers['type1']->expects($this->once())
         ->method('transform')
         ->with([$document1, $document2])
         ->will($this->returnValue([$result1, $result2]));

        $results = $this->collection->transform([$document1, $document2]);

        $this->assertSame([
            $result1,
            $result2,
        ], $results);
    }

    public function testGetIdentifierFieldReturnsAMapOfIdentifiers()
    {
        $collection = new ElasticaToModelTransformerCollection([]);
        $identifiers = $collection->getIdentifierField();
        $this->assertInternalType('array', $identifiers);
        $this->assertEmpty($identifiers);

        $this->collectionSetup();
        $identifiers = $this->collection->getIdentifierField();
        $this->assertInternalType('array', $identifiers);
        $this->assertSame(['type1' => 'id', 'type2' => 'id'], $identifiers);
    }

    public function elasticaResults()
    {
        $result = new Result(['_id' => 123, '_type' => 'type1']);
        $transformedObject = new POPO(123, []);

        return [
            [
                $result, $transformedObject,
            ],
        ];
    }

    /**
     * @dataProvider elasticaResults
     */
    public function testHybridTransformDecoratesResultsWithHybridResultObjects($result, $transformedObject)
    {
        $transformer = $this->getMockBuilder('FOS\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface')->getMock();
        $transformer->expects($this->any())->method('getIdentifierField')->will($this->returnValue('id'));

        $transformer
            ->expects($this->any())
            ->method('transform')
            ->will($this->returnValue([$transformedObject]));

        $collection = new ElasticaToModelTransformerCollection(['type1' => $transformer]);

        $hybridResults = $collection->hybridTransform([$result]);

        $this->assertInternalType('array', $hybridResults);
        $this->assertNotEmpty($hybridResults);
        $this->assertContainsOnlyInstancesOf('FOS\ElasticaBundle\HybridResult', $hybridResults);

        $hybridResult = array_pop($hybridResults);
        $this->assertSame($result, $hybridResult->getResult());
        $this->assertSame($transformedObject, $hybridResult->getTransformed());
    }

    protected function collectionSetup()
    {
        $transformer1 = $this->getMockBuilder('FOS\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface')->getMock();
        $transformer1->expects($this->any())
            ->method('getObjectClass')
            ->will($this->returnValue('FOS\ElasticaBundle\Tests\Transformer\POPO'));

        $transformer1->expects($this->any())
            ->method('getIdentifierField')
            ->will($this->returnValue('id'));

        $transformer2 = $this->getMockBuilder('FOS\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface')->getMock();
        $transformer2->expects($this->any())
            ->method('getObjectClass')
            ->will($this->returnValue('FOS\ElasticaBundle\Tests\Transformer\POPO2'));

        $transformer2->expects($this->any())
            ->method('getIdentifierField')
            ->will($this->returnValue('id'));

        $this->collection = new ElasticaToModelTransformerCollection($this->transformers = [
            'type1' => $transformer1,
            'type2' => $transformer2,
        ]);
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
