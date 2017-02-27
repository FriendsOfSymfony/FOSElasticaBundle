<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

    /**
     * @param string $testQuery
     * @param mixed  $testLimit
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
