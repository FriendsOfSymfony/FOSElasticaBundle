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
    public function testFind()
    {
        $testQuery = 'Test Query';

        $finderMock = $this->mockTransformedFinder('find', [$testQuery]);
        $repository = new Repository($finderMock);
        $repository->find($testQuery);
    }

    public function testFindWithLimit()
    {
        $testQuery = 'Test Query';
        $testLimit = 20;

        $finderMock = $this->mockTransformedFinder('find', [$testQuery, $testLimit]);
        $repository = new Repository($finderMock);
        $repository->find($testQuery, $testLimit);
    }

    public function testFindPaginated()
    {
        $testQuery = 'Test Query';

        $finderMock = $this->mockTransformedFinder('findPaginated', [$testQuery, []]);
        $repository = new Repository($finderMock);
        $repository->findPaginated($testQuery);
    }

    public function testCreatePagitatorAdapter()
    {
        $testQuery = 'Test Query';

        $finderMock = $this->mockTransformedFinder('createPaginatorAdapter', [$testQuery, []]);
        $repository = new Repository($finderMock);
        $repository->createPaginatorAdapter($testQuery);
    }

    public function testCreateHybridPaginatorAdapter()
    {
        $testQuery = 'Test Query';

        $finderMock = $this->mockTransformedFinder('createHybridPaginatorAdapter', [$testQuery]);
        $repository = new Repository($finderMock);
        $repository->createHybridPaginatorAdapter($testQuery);
    }

    public function testFindHybrid()
    {
        $testQuery = 'Test Query';

        $finderMock = $this->mockTransformedFinder('findHybrid', [$testQuery, null, []]);
        $repository = new Repository($finderMock);
        $repository->findHybrid($testQuery);
    }

    private function mockTransformedFinder($name, $arguments)
    {
        $finderMock = $this->createMock(TransformedFinder::class);
        $finderMock->expects($this->once())
            ->method($name)
            ->withConsecutive($arguments);
        return $finderMock;
    }
}
