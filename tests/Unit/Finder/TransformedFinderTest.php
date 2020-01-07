<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Finder;

use Elastica\Query;
use Elastica\ResultSet;
use Elastica\SearchableInterface;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use FOS\ElasticaBundle\Paginator\HybridPaginatorAdapter;
use FOS\ElasticaBundle\Paginator\TransformedPaginatorAdapter;
use FOS\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface;
use Pagerfanta\Pagerfanta;
use PHPUnit\Framework\TestCase;

class TransformedFinderTest extends TestCase
{
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
        $searchable = $this->createMock(SearchableInterface::class);
        $transformer = $this->createMock(ElasticaToModelTransformerInterface::class);

        $searchable->expects($this->once())
            ->method('search')
            ->with($this->isInstanceOf(Query::class))
            ->will($this->returnValue($this->createMockResultSet()));

        $finder = new TransformedFinder($searchable, $transformer);

        $method = new \ReflectionMethod($finder, 'search');
        $method->setAccessible(true);

        $results = $method->invoke($finder, '', 10);

        $this->assertInternalType('array', $results);
    }

    public function testFindHybridPaginatedReturnsAConfiguredPagerfantaObject()
    {
        $searchable = $this->createMock(SearchableInterface::class);
        $transformer = $this->createMock(ElasticaToModelTransformerInterface::class);

        $finder = new TransformedFinder($searchable, $transformer);

        $pagerfanta = $finder->findHybridPaginated('');

        $this->assertInstanceOf(Pagerfanta::class, $pagerfanta);
    }

    public function testFindPaginatedReturnsAConfiguredPagerfantaObject()
    {
        $searchable = $this->createMock(SearchableInterface::class);
        $transformer = $this->createMock(ElasticaToModelTransformerInterface::class);

        $finder = new TransformedFinder($searchable, $transformer);

        $pagerfanta = $finder->findPaginated('');

        $this->assertInstanceOf(Pagerfanta::class, $pagerfanta);
    }

    public function testCreatePaginatorAdapter()
    {
        $searchable = $this->createMock(SearchableInterface::class);
        $transformer = $this->createMock(ElasticaToModelTransformerInterface::class);

        $finder = new TransformedFinder($searchable, $transformer);

        $this->assertInstanceOf(TransformedPaginatorAdapter::class, $finder->createPaginatorAdapter(''));
    }

    public function testCreateHybridPaginatorAdapter()
    {
        $searchable = $this->createMock(SearchableInterface::class);
        $transformer = $this->createMock(ElasticaToModelTransformerInterface::class);

        $finder = new TransformedFinder($searchable, $transformer);

        $this->assertInstanceOf(HybridPaginatorAdapter::class, $finder->createHybridPaginatorAdapter(''));
    }

    private function createMockTransformer($transformMethod)
    {
        $transformer = $this->createMock(ElasticaToModelTransformerInterface::class);

        $transformer
            ->expects($this->once())
            ->method($transformMethod)
            ->with([]);

        return $transformer;
    }

    private function createMockFinderForSearch($transformer, $query, $limit)
    {
        $searchable = $this->createMock(SearchableInterface::class);

        $finder = $this->getMockBuilder(TransformedFinder::class)
            ->setConstructorArgs([$searchable, $transformer])
            ->setMethods(['search'])
            ->getMock();

        $finder
            ->expects($this->once())
            ->method('search')
            ->with($query, $limit)
            ->will($this->returnValue([]));

        return $finder;
    }

    private function createMockResultSet()
    {
        $resultSet = $this->createPartialMock(ResultSet::class, ['getResults']);

        $resultSet->expects($this->once())->method('getResults')->will($this->returnValue([]));

        return $resultSet;
    }
}
