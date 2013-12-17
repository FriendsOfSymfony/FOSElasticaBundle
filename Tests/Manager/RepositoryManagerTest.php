<?php

namespace FOS\ElasticaBundle\Tests\Manager;

use FOS\ElasticaBundle\Manager\RepositoryManager;

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
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\FOS\ElasticaBundle\Finder\TransformedFinder
     */
    private function createFinderMock()
    {
        $finderMock = $this->getMockBuilder('FOS\ElasticaBundle\Finder\TransformedFinder')
            ->disableOriginalConstructor()
            ->getMock();

        return $finderMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Doctrine\Common\Annotations\Reader
     */
    private function createReaderMock()
    {
        $readerMock = $this->getMockBuilder('Doctrine\Common\Annotations\Reader')
            ->disableOriginalConstructor()
            ->getMock();

        return $readerMock;
    }

    public function testThatGetRepositoryReturnsDefaultRepository()
    {
        $finderMock = $this->createFinderMock();
        $readerMock = $this->createReaderMock();

        $entityName = 'FOS\ElasticaBundle\Tests\Manager\Entity';

        $manager = new RepositoryManager($readerMock);
        $manager->addEntity($entityName, $finderMock);
        $repository = $manager->getRepository($entityName);
        $this->assertInstanceOf('FOS\ElasticaBundle\Repository', $repository);
    }

    public function testThatGetRepositoryReturnsCustomRepository()
    {
        $finderMock = $this->createFinderMock();
        $readerMock = $this->createReaderMock();

        $entityName = 'FOS\ElasticaBundle\Tests\Manager\Entity';

        $manager = new RepositoryManager($readerMock);
        $manager->addEntity($entityName, $finderMock, 'FOS\ElasticaBundle\Tests\Manager\CustomRepository');
        $repository = $manager->getRepository($entityName);
        $this->assertInstanceOf('FOS\ElasticaBundle\Tests\Manager\CustomRepository', $repository);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testThatGetRepositoryThrowsExceptionIfEntityNotConfigured()
    {
        $finderMock = $this->createFinderMock();
        $readerMock = $this->createReaderMock();

        $entityName = 'FOS\ElasticaBundle\Tests\Manager\Entity';

        $manager = new RepositoryManager($readerMock);
        $manager->addEntity($entityName, $finderMock);
        $manager->getRepository('Missing Entity');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testThatGetRepositoryThrowsExceptionIfCustomRepositoryNotFound()
    {
        $finderMock = $this->createFinderMock();
        $readerMock = $this->createReaderMock();

        $entityName = 'FOS\ElasticaBundle\Tests\Manager\Entity';

        $manager = new RepositoryManager($readerMock);
        $manager->addEntity($entityName, $finderMock, 'FOS\ElasticaBundle\Tests\MissingRepository');
        $manager->getRepository('Missing Entity');
    }

    public function testThatGetRepositoryCachesRepositoryInstances()
    {
        $finderMock = $this->createFinderMock();
        $readerMock = $this->createReaderMock();

        $entityName = 'FOS\ElasticaBundle\Tests\Manager\Entity';

        $manager = new RepositoryManager($readerMock);
        $manager->addEntity($entityName, $finderMock, 'FOS\ElasticaBundle\Tests\Manager\CustomRepository');
        $repository = $manager->getRepository($entityName);
        $this->assertInstanceOf('FOS\ElasticaBundle\Tests\Manager\CustomRepository', $repository);

        $repository2 = $manager->getRepository($entityName);
        $this->assertInstanceOf('FOS\ElasticaBundle\Tests\Manager\CustomRepository', $repository2);
        $this->assertSame($repository, $repository2);
    }
}
