<?php

namespace FOS\ElasticaBundle\Tests\Doctrine;

use Elastica\Result;
use FOS\ElasticaBundle\Transformer\HighlightableModelInterface;

class AbstractElasticaToModelTransformerTest extends \PHPUnit_Framework_TestCase
{
    private function createMockPropertyAccessor()
    {
        $callback = function ($object, $identifier) {
            return $object->$identifier;
        };

        $propertyAccessor = $this->getMock('Symfony\Component\PropertyAccess\PropertyAccessorInterface');
        $propertyAccessor
            ->expects($this->any())
            ->method('getValue')
            ->with($this->isType('object'), $this->isType('string'))
            ->will($this->returnCallback($callback));

        return $propertyAccessor;
    }

    /**
     * @param array $options
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\FOS\ElasticaBundle\Doctrine\AbstractElasticaToModelTransformer
     */
    private function createMockTransformer($options = array())
    {
        $objectClass = 'FOS\ElasticaBundle\Tests\Doctrine\Foo';
        $propertyAccessor = $this->createMockPropertyAccessor();

        $transformer = $this->getMockForAbstractClass(
            'FOS\ElasticaBundle\Doctrine\AbstractElasticaToModelTransformer',
            array(null, $objectClass, $options)
        );

        $transformer->setPropertyAccessor($propertyAccessor);

        return $transformer;
    }

    public function testObjectClassCanBeSet()
    {
        $transformer = $this->createMockTransformer();
        $this->assertEquals('FOS\ElasticaBundle\Tests\Doctrine\Foo', $transformer->getObjectClass());
    }

    public function resultsWithMatchingObjects()
    {
        $elasticaResults = $doctrineObjects = array();
        for ($i=1; $i<4; $i++) {
            $elasticaResults[] = new Result(array('_id' => $i, 'highlight' => array('foo')));
            $doctrineObjects[] = new Foo($i);
        }

        return array(
            array($elasticaResults, $doctrineObjects)
        );
    }

    /**
     * @dataProvider resultsWithMatchingObjects
     */
    public function testObjectsAreTransformedByFindingThemByTheirIdentifiers($elasticaResults, $doctrineObjects)
    {
        $transformer = $this->createMockTransformer();

        $transformer
            ->expects($this->once())
            ->method('findByIdentifiers')
            ->with($this->equalTo(array(1, 2, 3)), $this->isType('boolean'))
            ->will($this->returnValue($doctrineObjects));

        $transformedObjects = $transformer->transform($elasticaResults);

        $this->assertEquals($doctrineObjects, $transformedObjects);
    }

    /**
     * @dataProvider resultsWithMatchingObjects
     */
    public function testAnExceptionIsThrownWhenTheNumberOfFoundObjectsIsLessThanTheNumberOfResults(
        $elasticaResults,
        $doctrineObjects
    ) {
        $transformer = $this->createMockTransformer();

        $transformer
            ->expects($this->once())
            ->method('findByIdentifiers')
            ->with($this->equalTo(array(1, 2, 3)), $this->isType('boolean'))
            ->will($this->returnValue(array()));

        $this->setExpectedException(
            '\RuntimeException',
            'Cannot find corresponding Doctrine objects for all Elastica results.'
        );

        $transformer->transform($elasticaResults);
    }

    /**
     * @dataProvider resultsWithMatchingObjects
     */
    public function testAnExceptionIsNotThrownWhenTheNumberOfFoundObjectsIsLessThanTheNumberOfResultsIfOptionSet(
        $elasticaResults,
        $doctrineObjects
    ) {
        $transformer = $this->createMockTransformer(array('ignore_missing' => true));

        $transformer
            ->expects($this->once())
            ->method('findByIdentifiers')
            ->with($this->equalTo(array(1, 2, 3)), $this->isType('boolean'))
            ->will($this->returnValue(array()));

        $results = $transformer->transform($elasticaResults);

        $this->assertEquals(array(), $results);
    }

    /**
     * @dataProvider resultsWithMatchingObjects
     */
    public function testHighlightsAreSetOnTransformedObjects($elasticaResults, $doctrineObjects)
    {
        $transformer = $this->createMockTransformer();

        $transformer
            ->expects($this->once())
            ->method('findByIdentifiers')
            ->with($this->equalTo(array(1, 2, 3)), $this->isType('boolean'))
            ->will($this->returnValue($doctrineObjects));

        $results = $transformer->transform($elasticaResults);

        foreach($results as $result) {
            $this->assertInternalType('array', $result->highlights);
            $this->assertNotEmpty($result->highlights);
        }
    }

    /**
     * @dataProvider resultsWithMatchingObjects
     */
    public function testResultsAreSortedByIdentifier($elasticaResults, $doctrineObjects)
    {
        rsort($doctrineObjects);

        $transformer = $this->createMockTransformer();

        $transformer
            ->expects($this->once())
            ->method('findByIdentifiers')
            ->with($this->equalTo(array(1, 2, 3)), $this->isType('boolean'))
            ->will($this->returnValue($doctrineObjects));

        $results = $transformer->transform($elasticaResults);

        $this->assertSame($doctrineObjects[2], $results[0]);
        $this->assertSame($doctrineObjects[1], $results[1]);
        $this->assertSame($doctrineObjects[0], $results[2]);
    }

    /**
     * @dataProvider resultsWithMatchingObjects
     */
    public function testHybridTransformReturnsDecoratedResults($elasticaResults, $doctrineObjects)
    {
        $transformer = $this->createMockTransformer();

        $transformer
            ->expects($this->once())
            ->method('findByIdentifiers')
            ->with($this->equalTo(array(1, 2, 3)), $this->isType('boolean'))
            ->will($this->returnValue($doctrineObjects));

        $results = $transformer->hybridTransform($elasticaResults);

        $this->assertNotEmpty($results);

        foreach ($results as $key => $result) {
            $this->assertInstanceOf('FOS\ElasticaBundle\HybridResult', $result);
            $this->assertSame($elasticaResults[$key], $result->getResult());
            $this->assertSame($doctrineObjects[$key], $result->getTransformed());
        }
    }

    public function testIdentifierFieldDefaultsToId()
    {
        $transformer = $this->createMockTransformer();
        $this->assertEquals('id', $transformer->getIdentifierField());
    }
}

class Foo implements HighlightableModelInterface
{
    public $id;
    public $highlights;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setElasticHighlights(array $highlights)
    {
        $this->highlights = $highlights;
    }
}