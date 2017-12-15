<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Manager;

use FOS\ElasticaBundle\Finder\TransformedFinder;
use FOS\ElasticaBundle\Manager\RepositoryManager;
use FOS\ElasticaBundle\Repository;
use PHPUnit\Framework\TestCase;

class CustomRepository
{
}

class Entity
{
}

/**
 * @author Richard Miller <info@limethinking.co.uk>
 */
class RepositoryManagerTest extends TestCase
{
    public function testThatGetRepositoryReturnsDefaultRepository()
    {
        $finderMock = $this->createMock(TransformedFinder::class);

        $typeName = 'index/type';

        $manager = new RepositoryManager();
        $manager->addType($typeName, $finderMock);
        $repository = $manager->getRepository($typeName);
        $this->assertInstanceOf(Repository::class, $repository);
    }

    public function testThatGetRepositoryReturnsCustomRepository()
    {
        $finderMock = $this->createMock(TransformedFinder::class);

        $typeName = 'index/type';

        $manager = new RepositoryManager();
        $manager->addType($typeName, $finderMock, CustomRepository::class);
        $repository = $manager->getRepository($typeName);
        $this->assertInstanceOf(CustomRepository::class, $repository);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testThatGetRepositoryThrowsExceptionIfEntityNotConfigured()
    {
        $finderMock = $this->createMock(TransformedFinder::class);

        $typeName = 'index/type';

        $manager = new RepositoryManager();
        $manager->addType($typeName, $finderMock);
        $manager->getRepository('Missing type');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testThatGetRepositoryThrowsExceptionIfCustomRepositoryNotFound()
    {
        $finderMock = $this->createMock(TransformedFinder::class);

        $typeName = 'index/type';

        $manager = new RepositoryManager();
        $manager->addType($typeName, $finderMock, 'FOS\ElasticaBundle\Tests\MissingRepository');
        $manager->getRepository($typeName);
    }
}
