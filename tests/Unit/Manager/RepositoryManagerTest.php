<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Manager;

use FOS\ElasticaBundle\Finder\TransformedFinder;
use FOS\ElasticaBundle\Manager\RepositoryManager;
use FOS\ElasticaBundle\Repository;
use PHPUnit\Framework\TestCase;

class CustomRepository extends Repository
{
}

class Entity
{
}

/**
 * @author Richard Miller <info@limethinking.co.uk>
 *
 * @internal
 */
class RepositoryManagerTest extends TestCase
{
    public function testThatGetRepositoryReturnsDefaultRepository()
    {
        $finderMock = $this->createMock(TransformedFinder::class);

        $indexName = 'index';

        $manager = new RepositoryManager();
        $manager->addIndex($indexName, $finderMock);
        $repository = $manager->getRepository($indexName);
        $this->assertInstanceOf(Repository::class, $repository);
    }

    public function testThatGetRepositoryReturnsCustomRepository()
    {
        $finderMock = $this->createMock(TransformedFinder::class);

        $indexName = 'index';

        $manager = new RepositoryManager();
        $manager->addIndex($indexName, $finderMock, CustomRepository::class);
        $repository = $manager->getRepository($indexName);
        $this->assertInstanceOf(CustomRepository::class, $repository);
    }

    public function testThatGetRepositoryThrowsExceptionIfEntityNotConfigured()
    {
        $finderMock = $this->createMock(TransformedFinder::class);

        $indexName = 'index';

        $manager = new RepositoryManager();
        $manager->addIndex($indexName, $finderMock);

        $this->expectException(\RuntimeException::class);
        $manager->getRepository('Missing type');
    }

    public function testThatGetRepositoryThrowsExceptionIfCustomRepositoryNotFound()
    {
        $finderMock = $this->createMock(TransformedFinder::class);

        $indexName = 'index';

        $manager = new RepositoryManager();
        $manager->addIndex($indexName, $finderMock, 'FOS\ElasticaBundle\Tests\MissingRepository');

        $this->expectException(\RuntimeException::class);
        $manager->getRepository($indexName);
    }
}
