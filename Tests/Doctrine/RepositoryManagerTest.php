<?php

namespace FOQ\ElasticaBundle\Tests\Doctrine;

use FOQ\ElasticaBundle\Doctrine\RepositoryManager;

class CustomRepository{}

class Entity{}

/**
 * @author Richard Miller <info@limethinking.co.uk>
 */
class RepositoryManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
       if (!interface_exists('Doctrine\Common\Persistence\ManagerRegistry')) {
           $this->markTestSkipped('Doctrine Common is not available.');
       }
    }

    public function testThatGetRepositoryReturnsDefaultRepository()
    {
        $finderMock = $this->getMockBuilder('FOQ\ElasticaBundle\Finder\TransformedFinder')
            ->disableOriginalConstructor()
            ->getMock();

        $registryMock = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $readerMock = $this->getMockBuilder('Doctrine\Common\Annotations\Reader')
            ->disableOriginalConstructor()
            ->getMock();

        $entityName = 'FOQ\ElasticaBundle\Tests\Manager\Entity';

        $manager = new RepositoryManager($registryMock, $readerMock);
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

        $readerMock = $this->getMockBuilder('Doctrine\Common\Annotations\Reader')
            ->disableOriginalConstructor()
            ->getMock();

        $entityName = 'FOQ\ElasticaBundle\Tests\Manager\Entity';

        $manager = new RepositoryManager($registryMock, $readerMock);
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

        $readerMock = $this->getMockBuilder('Doctrine\Common\Annotations\Reader')
            ->disableOriginalConstructor()
            ->getMock();

        $entityName = 'FOQ\ElasticaBundle\Tests\Manager\Entity';

        $manager = new RepositoryManager($registryMock, $readerMock);
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

        $readerMock = $this->getMockBuilder('Doctrine\Common\Annotations\Reader')
            ->disableOriginalConstructor()
            ->getMock();

        $entityName = 'FOQ\ElasticaBundle\Tests\Manager\Entity';

        $manager = new RepositoryManager($registryMock, $readerMock);
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

        $readerMock = $this->getMockBuilder('Doctrine\Common\Annotations\Reader')
            ->disableOriginalConstructor()
            ->getMock();

        $shortEntityName = 'TestBundle:Entity';
        $entityName = 'FOQ\ElasticaBundle\Tests\Manager\Entity';
        $shortPath = 'TestBundle';
        $fullPath = 'FOQ\ElasticaBundle\Tests\Manager';

        $registryMock->expects($this->once())
            ->method('getAliasNamespace')
            ->with($this->equalTo($shortPath))
            ->will($this->returnValue($fullPath));

        $manager = new RepositoryManager($registryMock, $readerMock);
        $manager->addEntity($entityName, $finderMock);
        $repository = $manager->getRepository($shortEntityName);
        $this->assertInstanceOf('FOQ\ElasticaBundle\Repository', $repository);
    }

}
