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

use FOS\ElasticaBundle\Doctrine\RepositoryManager;
use FOS\ElasticaBundle\Repository;

class CustomRepository
{
}

class Entity
{
}

/**
 * @author Richard Miller <info@limethinking.co.uk>
 */
class RepositoryManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testThatGetRepositoryCallsMainRepositoryManager()
    {
        /** @var $finderMock \PHPUnit_Framework_MockObject_MockObject|\FOS\ElasticaBundle\Finder\TransformedFinder */
        $finderMock = $this->getMockBuilder('FOS\ElasticaBundle\Finder\TransformedFinder')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var $registryMock \PHPUnit_Framework_MockObject_MockObject|\Doctrine\Common\Persistence\ManagerRegistry */
        $registryMock = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $mainManager = $this->getMockBuilder('FOS\ElasticaBundle\Manager\RepositoryManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $mainManager->method('getRepository')
            ->with($this->equalTo('index/type'))
            ->willReturn(new Repository($finderMock));

        $entityName = 'FOS\ElasticaBundle\Tests\Manager\Entity';

        $manager = new RepositoryManager($registryMock, $mainManager);
        $manager->addEntity($entityName, 'index/type');
        $repository = $manager->getRepository($entityName);
        $this->assertInstanceOf('FOS\ElasticaBundle\Repository', $repository);
    }

    public function testGetRepositoryShouldResolveEntityShortName()
    {
        /** @var $finderMock \PHPUnit_Framework_MockObject_MockObject|\FOS\ElasticaBundle\Finder\TransformedFinder */
        $finderMock = $this->getMockBuilder('FOS\ElasticaBundle\Finder\TransformedFinder')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var $registryMock \PHPUnit_Framework_MockObject_MockObject|\Doctrine\Common\Persistence\ManagerRegistry */
        $registryMock = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $registryMock->method('getAliasNamespace')
            ->with($this->equalTo('FOSElasticaBundle'))
            ->willReturn('FOS\ElasticaBundle\Tests\Manager');

        $mainManager = $this->getMockBuilder('FOS\ElasticaBundle\Manager\RepositoryManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $mainManager->method('getRepository')
            ->with($this->equalTo('index/type'))
            ->willReturn(new Repository($finderMock));

        $entityName = 'FOS\ElasticaBundle\Tests\Manager\Entity';

        $manager = new RepositoryManager($registryMock, $mainManager);
        $manager->addEntity($entityName, 'index/type');
        $repository = $manager->getRepository('FOSElasticaBundle:Entity');
        $this->assertInstanceOf('FOS\ElasticaBundle\Repository', $repository);
    }
}
