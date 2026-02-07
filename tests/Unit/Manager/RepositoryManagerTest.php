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
use Symfony\Component\DependencyInjection\ServiceLocator;

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

        $manager = new RepositoryManager(new ServiceLocator([]));
        $manager->addIndex($indexName, $finderMock);
        $repository = $manager->getRepository($indexName);
        $this->assertInstanceOf(Repository::class, $repository);
    }

    public function testThatGetRepositoryReturnsCustomRepositoryFromLocator()
    {
        $finderMock = $this->createMock(TransformedFinder::class);
        $customRepository = new CustomRepository($finderMock);

        $indexName = 'index';

        $locator = new ServiceLocator([
            $indexName => static fn () => $customRepository,
        ]);

        $manager = new RepositoryManager($locator);
        $manager->addIndex($indexName, $finderMock, CustomRepository::class);
        $repository = $manager->getRepository($indexName);
        $this->assertInstanceOf(CustomRepository::class, $repository);
        $this->assertSame($customRepository, $repository);
    }

    public function testThatGetRepositoryReturnsCachedRepository()
    {
        $finderMock = $this->createMock(TransformedFinder::class);
        $customRepository = new CustomRepository($finderMock);

        $indexName = 'index';

        $locator = new ServiceLocator([
            $indexName => static fn () => $customRepository,
        ]);

        $manager = new RepositoryManager($locator);
        $manager->addIndex($indexName, $finderMock, CustomRepository::class);

        $first = $manager->getRepository($indexName);
        $second = $manager->getRepository($indexName);
        $this->assertSame($first, $second);
    }

    public function testThatGetRepositoryThrowsExceptionIfEntityNotConfigured()
    {
        $finderMock = $this->createMock(TransformedFinder::class);

        $indexName = 'index';

        $manager = new RepositoryManager(new ServiceLocator([]));
        $manager->addIndex($indexName, $finderMock);

        $this->expectException(\RuntimeException::class);
        $manager->getRepository('Missing type');
    }

    public function testThatLocatorIsPreferredOverDefaultRepository()
    {
        $finderMock = $this->createMock(TransformedFinder::class);
        $customRepository = new CustomRepository($finderMock);

        $indexName = 'index';

        $locator = new ServiceLocator([
            $indexName => static fn () => $customRepository,
        ]);

        $manager = new RepositoryManager($locator);
        // Even without specifying a custom repository name, locator takes precedence
        $manager->addIndex($indexName, $finderMock);
        $repository = $manager->getRepository($indexName);
        $this->assertSame($customRepository, $repository);
    }

    public function testThatDefaultRepositoryIsReturnedWhenNotInLocator()
    {
        $finderMock = $this->createMock(TransformedFinder::class);

        $locator = new ServiceLocator([
            'other_index' => static fn () => new CustomRepository($finderMock),
        ]);

        $manager = new RepositoryManager($locator);
        $manager->addIndex('my_index', $finderMock);
        $repository = $manager->getRepository('my_index');
        $this->assertInstanceOf(Repository::class, $repository);
        $this->assertNotInstanceOf(CustomRepository::class, $repository);
    }
}
