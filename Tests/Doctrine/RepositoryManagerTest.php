<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Doctrine;

use Doctrine\Common\Persistence\ManagerRegistry;
use FOS\ElasticaBundle\Doctrine\RepositoryManager;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use FOS\ElasticaBundle\Manager\RepositoryManagerInterface;
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
    public function testThatGetRepositoryCallsMainRepositoryManager()
    {
        $finderMock = $this->createMock(TransformedFinder::class);
        $registryMock = $this->createMock(ManagerRegistry::class);
        $mainManager = $this->createMock(RepositoryManagerInterface::class);

        $mainManager->method('getRepository')
            ->with($this->equalTo('index/type'))
            ->willReturn(new Repository($finderMock));

        $entityName = Entity::class;

        $manager = new RepositoryManager($registryMock, $mainManager);
        $manager->addEntity($entityName, 'index/type');
        $repository = $manager->getRepository($entityName);
        $this->assertInstanceOf(Repository::class, $repository);
    }

    public function testGetRepositoryShouldResolveEntityShortName()
    {
        $finderMock = $this->createMock(TransformedFinder::class);
        $registryMock = $this->createMock(ManagerRegistry::class);
        $mainManager = $this->createMock(RepositoryManagerInterface::class);

        $registryMock->method('getAliasNamespace')
            ->with($this->equalTo('FOSElasticaBundle'))
            ->willReturn('FOS\ElasticaBundle\Tests\Doctrine');

        $mainManager->method('getRepository')
            ->with($this->equalTo('index/type'))
            ->willReturn(new Repository($finderMock));

        $entityName = Entity::class;

        $manager = new RepositoryManager($registryMock, $mainManager);
        $manager->addEntity($entityName, 'index/type');
        $repository = $manager->getRepository('FOSElasticaBundle:Entity');
        $this->assertInstanceOf(Repository::class, $repository);
    }
}
