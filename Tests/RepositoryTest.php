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

        $finderMock = $this->getMockBuilder('FOS\ElasticaBundle\Finder\TransformedFinder')
            ->disableOriginalConstructor()
            ->getMock();
        $finderMock->expects($this->once())
            ->method('find')
            ->with($this->equalTo($testQuery));

        $repository = new Repository($finderMock);
        $repository->find($testQuery);
    }

    public function testThatFindCallsFindOnFinderWithLimit()
    {
        $testQuery = 'Test Query';
        $testLimit = 20;

        $finderMock = $this->getMockBuilder('FOS\ElasticaBundle\Finder\TransformedFinder')
            ->disableOriginalConstructor()
            ->getMock();
        $finderMock->expects($this->once())
            ->method('find')
            ->with($this->equalTo($testQuery), $this->equalTo($testLimit));

        $repository = new Repository($finderMock);
        $repository->find($testQuery, $testLimit);
    }

    public function testThatFindPaginatedCallsFindPaginatedOnFinder()
    {
        $testQuery = 'Test Query';

        $finderMock = $this->getMockBuilder('FOS\ElasticaBundle\Finder\TransformedFinder')
            ->disableOriginalConstructor()
            ->getMock();
        $finderMock->expects($this->once())
            ->method('findPaginated')
            ->with($this->equalTo($testQuery));

        $repository = new Repository($finderMock);
        $repository->findPaginated($testQuery);
    }

    public function testThatFindHybridCallsFindHybridOnFinder()
    {
        $testQuery = 'Test Query';
        $testLimit = 20;

        $finderMock = $this->getMockBuilder('FOS\ElasticaBundle\Finder\TransformedFinder')
            ->disableOriginalConstructor()
            ->getMock();
        $finderMock->expects($this->once())
            ->method('findHybrid')
            ->with($this->equalTo($testQuery), $this->equalTo($testLimit));

        $repository = new Repository($finderMock);
        $repository->findHybrid($testQuery, $testLimit);
    }

}
