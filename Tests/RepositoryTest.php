<?php

namespace FOS\ElasticaBundle\Tests;

use FOS\ElasticaBundle\Repository;

/**
 * @author Richard Miller <info@limethinking.co.uk>
 */
class RepositoryTest extends \PHPUnit_Framework_TestCase
{
    public function testThatFindCallsFindOnFinder()
    {
        $testQuery = 'Test Query';

        $finderMock = $this->getFinderMock($testQuery);
        $repository = new Repository($finderMock);
        $repository->find($testQuery);
    }

    public function testThatFindCallsFindOnFinderWithLimit()
    {
        $testQuery = 'Test Query';
        $testLimit = 20;

        $finderMock = $this->getFinderMock($testQuery, $testLimit);
        $repository = new Repository($finderMock);
        $repository->find($testQuery, $testLimit);
    }

    public function testThatFindPaginatedCallsFindPaginatedOnFinder()
    {
        $testQuery = 'Test Query';

        $finderMock = $this->getFinderMock($testQuery, array(), 'findPaginated');
        $repository = new Repository($finderMock);
        $repository->findPaginated($testQuery);
    }

    public function testThatCreatePaginatorCreatesAPaginatorViaFinder()
    {
        $testQuery = 'Test Query';

        $finderMock = $this->getFinderMock($testQuery, array(), 'createPaginatorAdapter');
        $repository = new Repository($finderMock);
        $repository->createPaginatorAdapter($testQuery);
    }

    public function testThatFindHybridCallsFindHybridOnFinder()
    {
        $testQuery = 'Test Query';

        $finderMock = $this->getFinderMock($testQuery, null, 'findHybrid');
        $repository = new Repository($finderMock);
        $repository->findHybrid($testQuery);
    }

    public function testThatFindRawResultCallsFindRawResultOnFinder()
    {
        $testQuery = 'Test Query';

        $finderMock = $this->getFinderMock($testQuery, null, 'findRawResult');
        $repository = new Repository($finderMock);
        $repository->findRawResult($testQuery);
    }
    
    public function testThatFindRawPaginatedCallsFindRawPaginatedOnFinder()
    {
        $testQuery = 'Test Query';

        $finderMock = $this->getFinderMock($testQuery, array(), 'findRawPaginated');
        $repository = new Repository($finderMock);
        $repository->findRawPaginated($testQuery);
    }

    public function testThatCreateRawPaginatorCreatesAPaginatorViaFinder()
    {
        $testQuery = 'Test Query';

        $finderMock = $this->getFinderMock($testQuery, array(), 'createRawPaginatorAdapter');
        $repository = new Repository($finderMock);
        $repository->createRawPaginatorAdapter($testQuery);
    }

    /**
     * @param string $testQuery
     * @param mixed $testLimit
     * @param string $method
     *
     * @return \FOS\ElasticaBundle\Finder\TransformedFinder
     */
    private function getFinderMock($testQuery, $testLimit = null, $method = 'find')
    {
        $finderMock = $this->getMockBuilder('FOS\ElasticaBundle\Finder\TransformedFinder')
            ->disableOriginalConstructor()
            ->getMock();
        $finderMock->expects($this->once())
            ->method($method)
            ->with($this->equalTo($testQuery), $this->equalTo($testLimit));

        return $finderMock;
    }
}
