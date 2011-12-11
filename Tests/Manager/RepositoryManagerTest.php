<?php

namespace FOQ\ElasticaBundle\Tests\Manager;

use FOQ\ElasticaBundle\Manager\RepositoryManager;

class CustomRepository{}

/**
 * @author Richard Miller <info@limethinking.co.uk>
 */
class RepositoryManagerTest extends \PHPUnit_Framework_TestCase
{

    public function testThatGetRepositoryReturnsDefaultRepository()
    {
        $finderMock = $this->getMockBuilder('FOQ\ElasticaBundle\Finder\TransformedFinder')
            ->disableOriginalConstructor()
            ->getMock();

        $registryMock = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $entityName = 'Test Entity';

        $manager = new RepositoryManager($registryMock);
        $manager->addEntity($entityName, $finderMock);
        $repository = $manager->getRepository($entityName);
        $this->assertInstanceOf('FOQ\ElasticaBundle\Repository', $repository);
    }

    public function testThatGetRepositoryReturnsCustomRepository()
    {
        $finderMock = $this->getMockBuilder('FOQ\ElasticaBundle\Finder\TransformedFinder')
            ->disableOriginalConstructor()
            ->getMock();

        $registryMock = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $entityName = 'Test Entity';

        $manager = new RepositoryManager($registryMock);
        $manager->addEntity($entityName, $finderMock, 'FOQ\ElasticaBundle\Tests\Manager\CustomRepository');
        $repository = $manager->getRepository($entityName);
        $this->assertInstanceOf('FOQ\ElasticaBundle\Tests\Manager\CustomRepository', $repository);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testThatGetRepositoryThrowsExceptionIfEntityNotConfigured()
    {
        $finderMock = $this->getMockBuilder('FOQ\ElasticaBundle\Finder\TransformedFinder')
            ->disableOriginalConstructor()
            ->getMock();

        $registryMock = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $entityName = 'Test Entity';

        $manager = new RepositoryManager($registryMock);
        $manager->addEntity($entityName, $finderMock);
        $manager->getRepository('Missing Entity');
    }

    /**
     * @expectedException RuntimeException
     */
    public function testThatGetRepositoryThrowsExceptionIfCustomRepositoryNotFound()
    {
        $finderMock = $this->getMockBuilder('FOQ\ElasticaBundle\Finder\TransformedFinder')
            ->disableOriginalConstructor()
            ->getMock();

        $registryMock = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $entityName = 'Test Entity';

        $manager = new RepositoryManager($registryMock);
        $manager->addEntity($entityName, $finderMock, 'FOQ\ElasticaBundle\Tests\MissingRepository');
        $manager->getRepository('Missing Entity');
    }

    public function testThatGetRepositoryWorksWithShortEntityName()
    {
        $finderMock = $this->getMockBuilder('FOQ\ElasticaBundle\Finder\TransformedFinder')
            ->disableOriginalConstructor()
            ->getMock();

        $registryMock = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $shortEntityName = 'TestBundle:TestEntity';
        $entityName = 'TestBundle\Full\Path\To\TestEntity';
        $shortPath = 'TestBundle';
        $fullPath = 'TestBundle\Full\Path\To';

        $registryMock->expects($this->once())
            ->method('getAliasNamespace')
            ->with($this->equalTo($shortPath))
            ->will($this->returnValue($fullPath));

        $manager = new RepositoryManager($registryMock);
        $manager->addEntity($entityName, $finderMock);
        $repository = $manager->getRepository($shortEntityName);
        $this->assertInstanceOf('FOQ\ElasticaBundle\Repository', $repository);
    }

}
