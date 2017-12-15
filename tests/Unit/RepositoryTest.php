<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit;

use FOS\ElasticaBundle\Finder\TransformedFinder;
use FOS\ElasticaBundle\Repository;
use PHPUnit\Framework\TestCase;

/**
 * @author Richard Miller <info@limethinking.co.uk>
 */
class RepositoryTest extends TestCase
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

        $finderMock = $this->getFinderMock($testQuery, [], 'findPaginated');
        $repository = new Repository($finderMock);
        $repository->findPaginated($testQuery);
    }

    public function testThatCreatePaginatorCreatesAPaginatorViaFinder()
    {
        $testQuery = 'Test Query';

        $finderMock = $this->getFinderMock($testQuery, [], 'createPaginatorAdapter');
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

    private function getFinderMock($testQuery, $testLimit = null, $method = 'find')
    {
        $finderMock = $this->createMock(TransformedFinder::class);
        $finderMock->expects($this->once())
            ->method($method)
            ->with($this->equalTo($testQuery), $this->equalTo($testLimit));

        return $finderMock;
    }
}
