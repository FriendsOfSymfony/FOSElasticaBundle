<?php

namespace FOQ\ElasticaBundle\Tests\Manager;

use FOQ\ElasticaBundle\Manager\RepositoryManager;

class CustomRepository{}

class Entity{}

/**
 * @author Richard Miller <info@limethinking.co.uk>
 */
class RepositoryManagerTest extends \PHPUnit_Framework_TestCase
{

    public function testThatGetRepositoryReturnsDefaultRepository()
    {
        /** @var $finderMock \PHPUnit_Framework_MockObject_MockObject|\FOQ\ElasticaBundle\Finder\TransformedFinder */
        $finderMock = $this->getMockBuilder('FOQ\ElasticaBundle\Finder\TransformedFinder')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var $readerMock \PHPUnit_Framework_MockObject_MockObject|\Doctrine\Common\Annotations\Reader */
        $readerMock = $this->getMockBuilder('Doctrine\Common\Annotations\Reader')
            ->disableOriginalConstructor()
            ->getMock();

        $entityName = 'FOQ\ElasticaBundle\Tests\Manager\Entity';

        $manager = new RepositoryManager($readerMock);
        $manager->addEntity($entityName, $finderMock);
        $repository = $manager->getRepository($entityName);
        $this->assertInstanceOf('FOQ\ElasticaBundle\Repository', $repository);
    }

    public function testThatGetRepositoryReturnsCustomRepository()
    {
        /** @var $finderMock \PHPUnit_Framework_MockObject_MockObject|\FOQ\ElasticaBundle\Finder\TransformedFinder */
        $finderMock = $this->getMockBuilder('FOQ\ElasticaBundle\Finder\TransformedFinder')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var $readerMock \PHPUnit_Framework_MockObject_MockObject|\Doctrine\Common\Annotations\Reader */
        $readerMock = $this->getMockBuilder('Doctrine\Common\Annotations\Reader')
            ->disableOriginalConstructor()
            ->getMock();

        $entityName = 'FOQ\ElasticaBundle\Tests\Manager\Entity';

        $manager = new RepositoryManager($readerMock);
        $manager->addEntity($entityName, $finderMock, 'FOQ\ElasticaBundle\Tests\Manager\CustomRepository');
        $repository = $manager->getRepository($entityName);
        $this->assertInstanceOf('FOQ\ElasticaBundle\Tests\Manager\CustomRepository', $repository);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testThatGetRepositoryThrowsExceptionIfEntityNotConfigured()
    {
        /** @var $finderMock \PHPUnit_Framework_MockObject_MockObject|\FOQ\ElasticaBundle\Finder\TransformedFinder */
        $finderMock = $this->getMockBuilder('FOQ\ElasticaBundle\Finder\TransformedFinder')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var $readerMock \PHPUnit_Framework_MockObject_MockObject|\Doctrine\Common\Annotations\Reader */
        $readerMock = $this->getMockBuilder('Doctrine\Common\Annotations\Reader')
            ->disableOriginalConstructor()
            ->getMock();

        $entityName = 'FOQ\ElasticaBundle\Tests\Manager\Entity';

        $manager = new RepositoryManager($readerMock);
        $manager->addEntity($entityName, $finderMock);
        $manager->getRepository('Missing Entity');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testThatGetRepositoryThrowsExceptionIfCustomRepositoryNotFound()
    {
        /** @var $finderMock \PHPUnit_Framework_MockObject_MockObject|\FOQ\ElasticaBundle\Finder\TransformedFinder */
        $finderMock = $this->getMockBuilder('FOQ\ElasticaBundle\Finder\TransformedFinder')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var $readerMock \PHPUnit_Framework_MockObject_MockObject|\Doctrine\Common\Annotations\Reader */
        $readerMock = $this->getMockBuilder('Doctrine\Common\Annotations\Reader')
            ->disableOriginalConstructor()
            ->getMock();

        $entityName = 'FOQ\ElasticaBundle\Tests\Manager\Entity';

        $manager = new RepositoryManager($readerMock);
        $manager->addEntity($entityName, $finderMock, 'FOQ\ElasticaBundle\Tests\MissingRepository');
        $manager->getRepository('Missing Entity');
    }

}
