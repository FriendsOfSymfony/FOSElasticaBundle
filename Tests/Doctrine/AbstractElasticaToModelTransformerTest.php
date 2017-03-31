<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Doctrine;

use Elastica\Result;
use FOS\ElasticaBundle\Transformer\HighlightableModelInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class AbstractElasticaToModelTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doctrine\Common\Persistence\ManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var string
     */
    protected $objectClass = 'stdClass';

    protected function setUp()
    {
        $this->registry = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Tests if ignore_missing option is properly handled in transformHybrid() method.
     */
    public function testIgnoreMissingOptionDuringTransformHybrid()
    {
        $transformer = $this->getMockBuilder('FOS\ElasticaBundle\Doctrine\ORM\ElasticaToModelTransformer')
            ->setMethods(['findByIdentifiers'])
            ->setConstructorArgs([$this->registry, $this->objectClass, ['ignore_missing' => true]])
            ->getMock();

        $transformer->setPropertyAccessor(PropertyAccess::createPropertyAccessor());

        $firstOrmResult = new \stdClass();
        $firstOrmResult->id = 1;
        $secondOrmResult = new \stdClass();
        $secondOrmResult->id = 3;
        $transformer->expects($this->once())
            ->method('findByIdentifiers')
            ->with([1, 2, 3])
            ->willReturn([$firstOrmResult, $secondOrmResult]);

        $firstElasticaResult = new Result(['_id' => 1]);
        $secondElasticaResult = new Result(['_id' => 2]);
        $thirdElasticaResult = new Result(['_id' => 3]);

        $hybridResults = $transformer->hybridTransform([$firstElasticaResult, $secondElasticaResult, $thirdElasticaResult]);

        $this->assertCount(2, $hybridResults);
        $this->assertSame($firstOrmResult, $hybridResults[0]->getTransformed());
        $this->assertSame($firstElasticaResult, $hybridResults[0]->getResult());
        $this->assertSame($secondOrmResult, $hybridResults[1]->getTransformed());
        $this->assertSame($thirdElasticaResult, $hybridResults[1]->getResult());
    }

    public function testObjectClassCanBeSet()
    {
        $transformer = $this->createMockTransformer();
        $this->assertSame('FOS\ElasticaBundle\Tests\Doctrine\Foo', $transformer->getObjectClass());
    }

    public function resultsWithMatchingObjects()
    {
        $elasticaResults = $doctrineObjects = [];
        for ($i = 1; $i < 4; ++$i) {
            $elasticaResults[] = new Result(['_id' => $i, 'highlight' => ['foo']]);
            $doctrineObjects[] = new Foo($i);
        }

        return [
            [$elasticaResults, $doctrineObjects],
        ];
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
            ->with($this->equalTo([1, 2, 3]), $this->isType('boolean'))
            ->will($this->returnValue($doctrineObjects));

        $transformedObjects = $transformer->transform($elasticaResults);

        $this->assertSame($doctrineObjects, $transformedObjects);
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
            ->with($this->equalTo([1, 2, 3]), $this->isType('boolean'))
            ->will($this->returnValue([]));

        $this->setExpectedException(
            '\RuntimeException',
            'Cannot find corresponding Doctrine objects (0) for all Elastica results (3). IDs: 1, 2, 3'
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
        $transformer = $this->createMockTransformer(['ignore_missing' => true]);

        $transformer
            ->expects($this->once())
            ->method('findByIdentifiers')
            ->with($this->equalTo([1, 2, 3]), $this->isType('boolean'))
            ->will($this->returnValue([]));

        $results = $transformer->transform($elasticaResults);

        $this->assertSame([], $results);
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
            ->with($this->equalTo([1, 2, 3]), $this->isType('boolean'))
            ->will($this->returnValue($doctrineObjects));

        $results = $transformer->transform($elasticaResults);

        foreach ($results as $result) {
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
            ->with($this->equalTo([1, 2, 3]), $this->isType('boolean'))
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
            ->with($this->equalTo([1, 2, 3]), $this->isType('boolean'))
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
        $this->assertSame('id', $transformer->getIdentifierField());
    }

    private function createMockPropertyAccessor()
    {
        $callback = function ($object, $identifier) {
            return $object->$identifier;
        };

        $propertyAccessor = $this->getMockBuilder('Symfony\Component\PropertyAccess\PropertyAccessorInterface')->getMock();
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
    private function createMockTransformer($options = [])
    {
        $objectClass = 'FOS\ElasticaBundle\Tests\Doctrine\Foo';
        $propertyAccessor = $this->createMockPropertyAccessor();

        $transformer = $this->getMockForAbstractClass(
            'FOS\ElasticaBundle\Doctrine\AbstractElasticaToModelTransformer',
            [$this->registry, $objectClass, $options]
        );

        $transformer->setPropertyAccessor($propertyAccessor);

        return $transformer;
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
