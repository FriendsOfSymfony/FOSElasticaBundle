<?php

namespace FOS\ElasticaBundle\Tests\Finder;

use FOS\ElasticaBundle\Finder\TransformedFinder;

class TransformedFinderTest extends \PHPUnit_Framework_TestCase
{
    public function testFindWithSize()
    {
        $searchable = $this->getSearchable();
        $transformer = $this->getTransformer();
        $resultSet = $this->getResulSet();
        $finder = new TransformedFinder($searchable, $transformer);
        
        $resultMock = $this->getMockBuilder('Elastica\Result')
            ->disableOriginalConstructor()
            ->getMock();

        $resultSet->expects($this->any())
            ->method('getResults')
            ->will($this->returnValue(array(
                $resultMock
            )));

        $searchable->expects($this->once())
            ->method('search')
            ->with($this->logicalAnd(
                $this->isInstanceOf('Elastica\Query'),
                $this->callback(function ($object) {
                    return $object->getParam('size') === 12;
                })
            ))
            ->will($this->returnValue($resultSet));

        $transformer->expects($this->once())
            ->method('transform')
            ->with($this->logicalAnd(
                $this->isType('array'),
                $this->callback(function ($results) {
                    return $results[0] instanceof \Elastica\Result;
                })
            ))
            ->will($this->returnValue(
                array()
            ));

        $this->assertEquals($finder->find('{}', 12), array());
    }

    public function testFindWithoutSize()
    {
        $searchable = $this->getSearchable();
        $transformer = $this->getTransformer();
        $resultSet = $this->getResulSet();
        $finder = new TransformedFinder($searchable, $transformer);

        $resultMock = $this->getMockBuilder('Elastica\Result')
            ->disableOriginalConstructor()
            ->getMock();

        $resultSet->expects($this->any())
            ->method('getResults')
            ->will($this->returnValue(array(
                $resultMock
            )));

        $searchable->expects($this->once())
            ->method('search')
            ->with($this->logicalAnd(
                $this->isInstanceOf('Elastica\Query'),
                $this->callback(function ($object) {
                    return $object->hasParam('size') === false;
                })
            ))
            ->will($this->returnValue($resultSet));

        $transformer->expects($this->once())
            ->method('transform')
            ->with($this->logicalAnd(
                $this->isType('array'),
                $this->callback(function ($results) {
                    return $results[0] instanceof \Elastica\Result;
                })
            ))
            ->will($this->returnValue(
                array()
            ));

        $this->assertEquals($finder->find('{}'), array());
    }

    public function testFindHybridWithSize()
    {
        $searchable = $this->getSearchable();
        $transformer = $this->getTransformer();
        $resultSet = $this->getResulSet();
        $finder = new TransformedFinder($searchable, $transformer);

        $resultMock = $this->getMockBuilder('Elastica\Result')
            ->disableOriginalConstructor()
            ->getMock();

        $hybridResultMock = $this->getMockBuilder('FOS\ElasticaBundle\HybridResult')
            ->disableOriginalConstructor()
            ->getMock();

        $resultSet->expects($this->any())
            ->method('getResults')
            ->will($this->returnValue(array(
                $resultMock
            )));

        $searchable->expects($this->once())
            ->method('search')
            ->with($this->logicalAnd(
                $this->isInstanceOf('Elastica\Query'),
                $this->callback(function ($object) {
                    return $object->getParam('size') === 12;
                })
            ))
            ->will($this->returnValue($resultSet));

        $transformer->expects($this->once())
            ->method('hybridTransform')
            ->with($this->logicalAnd(
                $this->isType('array'),
                $this->callback(function ($results) {
                    return $results[0] instanceof \Elastica\Result;
                })
            ))
            ->will($this->returnValue(
                array(
                    $hybridResultMock
                )
            ));

        $this->assertEquals($finder->findHybrid('{}', 12), array($hybridResultMock));
    }

    public function testFindHybridWithoutSize()
    {
        $searchable = $this->getSearchable();
        $transformer = $this->getTransformer();
        $resultSet = $this->getResulSet();
        $finder = new TransformedFinder($searchable, $transformer);

        $resultMock = $this->getMockBuilder('Elastica\Result')
            ->disableOriginalConstructor()
            ->getMock();

        $hybridResultMock = $this->getMockBuilder('FOS\ElasticaBundle\HybridResult')
            ->disableOriginalConstructor()
            ->getMock();

        $resultSet->expects($this->any())
            ->method('getResults')
            ->will($this->returnValue(array(
                $resultMock
            )));

        $searchable->expects($this->once())
            ->method('search')
            ->with($this->logicalAnd(
                $this->isInstanceOf('Elastica\Query'),
                $this->callback(function ($object) {
                    return $object->hasParam('size') === false;
                })
            ))
            ->will($this->returnValue($resultSet));

        $transformer->expects($this->once())
            ->method('hybridTransform')
            ->with($this->logicalAnd(
                $this->isType('array'),
                $this->callback(function ($results) {
                    return $results[0] instanceof \Elastica\Result;
                })
            ))
            ->will($this->returnValue(
                array(
                    $hybridResultMock
                )
            ));

        $this->assertEquals($finder->findHybrid('{}'), array($hybridResultMock));
    }

    public function testMoreLikeThis()
    {
        $searchable = $this->getSearchable();
        $transformer = $this->getTransformer();
        $resultSet = $this->getResulSet();
        $finder = new TransformedFinder($searchable, $transformer);

        $resultMock = $this->getMockBuilder('Elastica\Result')
            ->disableOriginalConstructor()
            ->getMock();

        $resultSet->expects($this->any())
            ->method('getResults')
            ->will($this->returnValue(array(
                $resultMock
            )));

        $searchable->expects($this->any())
            ->method('moreLikeThis')
            ->with(
                $this->isInstanceOf('Elastica\Document'),
                $this->isType('array'),
                $this->logicalOr(
                    $this->isType('array'),
                    $this->isType('string'),
                    $this->isInstanceOf('Elastica\Query')
                )
            )
            ->will($this->returnValue($resultSet));

        $transformer->expects($this->any())
            ->method('transform')
            ->with($this->logicalAnd(
                $this->isType('array'),
                $this->callback(function ($results) {
                    return $results[0] instanceof \Elastica\Result;
                })
            ))
            ->will($this->returnValue(
                array()
            ));

        $this->assertEquals($finder->moreLikeThis(1), array());
    }

    public function testFindPaginated()
    {
        $searchable = $this->getSearchable();
        $transformer = $this->getTransformer();
        $finder = new TransformedFinder($searchable, $transformer);

        $this->assertInstanceOf('Pagerfanta\Pagerfanta',$finder->findPaginated('{}'));
    }

    public function testCreatePaginatorAdapter()
    {
        $searchable = $this->getSearchable();
        $transformer = $this->getTransformer();
        $finder = new TransformedFinder($searchable, $transformer);

        $this->assertInstanceOf('FOS\ElasticaBundle\Paginator\TransformedPaginatorAdapter',$finder->createPaginatorAdapter('{}'));
    }

    public function testCreateHybridPaginatorAdapter()
    {
        $searchable = $this->getSearchable();
        $transformer = $this->getTransformer();
        $finder = new TransformedFinder($searchable, $transformer);

        $this->assertInstanceOf('FOS\ElasticaBundle\Paginator\HybridPaginatorAdapter',$finder->createHybridPaginatorAdapter('{}'));
    }

    private function getTransformer()
    {
        return $this->getMock('FOS\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface');
    }

    private function getSearchable()
    {
        return $this->getMockBuilder('Elastica\Type')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getResulSet()
    {
        return $this->getMockBuilder('Elastica\ResultSet')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
