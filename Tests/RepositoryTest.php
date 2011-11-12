<?php

namespace FOQ\ElasticaBundle\Tests;

use FOQ\ElasticaBundle\Repository;

/**
 * @author Richard Miller <info@limethinking.co.uk>
 */
class RepositoryTest extends \PHPUnit_Framework_TestCase
{

    public function testThatFindCallsFindOnFinder()
    {
        $testQuery = 'Test Query';

        $finderMock = $this->getMockBuilder('FOQ\ElasticaBundle\Finder\TransformedFinder')
            ->disableOriginalConstructor()
            ->getMock();
        $finderMock->expects($this->once())
            ->method('find')
            ->with($this->equalTo($testQuery));

        $repository = new Repository($finderMock);
        $repository->find($testQuery);
    }

    public function testThatFindPaginatedCallsFindPaginatedOnFinder()
    {
        $testQuery = 'Test Query';

        $finderMock = $this->getMockBuilder('FOQ\ElasticaBundle\Finder\TransformedFinder')
            ->disableOriginalConstructor()
            ->getMock();
        $finderMock->expects($this->once())
            ->method('findPaginated')
            ->with($this->equalTo($testQuery));

        $repository = new Repository($finderMock);
        $repository->findPaginated($testQuery);
    }

}
