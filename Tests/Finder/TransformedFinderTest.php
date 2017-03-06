<?php

namespace FOS\ElasticaBundle\Tests\Finder;

use Elastica\Query;
use FOS\ElasticaBundle\Finder\TransformedFinder;

class TransformedFinderTest extends \PHPUnit_Framework_TestCase
{
    private function createMockTransformer($transformMethod)
    {
        $transformer = $this->getMockBuilder('FOS\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface')->getMock();

        $transformer
            ->expects($this->once())
            ->method($transformMethod)
            ->with(array());

        return $transformer;
    }

    private function createMockFinderForSearch($transformer, $query, $limit)
    {
        $searchable = $this->getMockBuilder('Elastica\SearchableInterface')->getMock();

        $finder = $this->getMockBuilder('FOS\ElasticaBundle\Finder\TransformedFinder')
            ->setConstructorArgs(array($searchable, $transformer))
            ->setMethods(array('search'))
            ->getMock();

        $finder
            ->expects($this->once())
            ->method('search')
            ->with($query, $limit)
            ->will($this->returnValue(array()));

        return $finder;
    }

    private function createMockResultSet()
    {
        $resultSet = $this
            ->getMockBuilder('Elastica\ResultSet')
            ->disableOriginalConstructor()
            ->setMethods(array('getResults'))
            ->getMock();

        $resultSet->expects($this->once())->method('getResults')->will($this->returnValue(array()));

        return $resultSet;
    }

    public function testFindMethodTransformsSearchResults()
    {
        $transformer = $this->createMockTransformer('transform');
        $query = Query::create('');
        $limit = 10;

        $finder = $this->createMockFinderForSearch($transformer, $query, $limit);

        $finder->find($query, $limit);
    }

    public function testFindHybridMethodTransformsSearchResults()
    {
        $transformer = $this->createMockTransformer('hybridTransform');
        $query = Query::create('');
        $limit = 10;

        $finder = $this->createMockFinderForSearch($transformer, $query, $limit);

        $finder->findHybrid($query, $limit);
    }

    public function testSearchMethodCreatesAQueryAndReturnsResultsFromSearchableDependency()
    {
        $searchable = $this->getMockBuilder('Elastica\SearchableInterface')->getMock();
        $transformer = $this->getMockBuilder('FOS\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface')->getMock();

        $searchable->expects($this->once())
            ->method('search')
            ->with($this->isInstanceOf('Elastica\Query'))
            ->will($this->returnValue($this->createMockResultSet()));

        $finder = new TransformedFinder($searchable, $transformer);

        $method = new \ReflectionMethod($finder, 'search');
        $method->setAccessible(true);

        $results = $method->invoke($finder, '', 10);

        $this->assertInternalType('array', $results);
    }

    public function testFindPaginatedReturnsAConfiguredPagerfantaObject()
    {
        $searchable = $this->getMockBuilder('Elastica\SearchableInterface')->getMock();
        $transformer = $this->getMockBuilder('FOS\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface')->getMock();

        $finder = new TransformedFinder($searchable, $transformer);

        $pagerfanta = $finder->findPaginated('');

        $this->assertInstanceOf('Pagerfanta\Pagerfanta', $pagerfanta);
    }

    public function testCreatePaginatorAdapter()
    {
        $searchable = $this->getMockBuilder('Elastica\SearchableInterface')->getMock();
        $transformer = $this->getMockBuilder('FOS\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface')->getMock();

        $finder = new TransformedFinder($searchable, $transformer);

        $this->assertInstanceOf('FOS\ElasticaBundle\Paginator\TransformedPaginatorAdapter', $finder->createPaginatorAdapter(''));
    }

    public function testCreateHybridPaginatorAdapter()
    {
        $searchable = $this->getMockBuilder('Elastica\SearchableInterface')->getMock();
        $transformer = $this->getMockBuilder('FOS\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface')->getMock();

        $finder = new TransformedFinder($searchable, $transformer);

        $this->assertInstanceOf('FOS\ElasticaBundle\Paginator\HybridPaginatorAdapter', $finder->createHybridPaginatorAdapter(''));
    }
}
