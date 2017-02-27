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
    public function testThatGetRepositoryReturnsDefaultRepository()
    {
        /** @var $finderMock \PHPUnit_Framework_MockObject_MockObject|\FOS\ElasticaBundle\Finder\TransformedFinder */
        $finderMock = $this->getMockBuilder('FOS\ElasticaBundle\Finder\TransformedFinder')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var $readerMock \PHPUnit_Framework_MockObject_MockObject|\Doctrine\Common\Annotations\Reader */
        $readerMock = $this->getMockBuilder('Doctrine\Common\Annotations\Reader')
            ->disableOriginalConstructor()
            ->getMock();

        $typeName = 'index/type';

        $manager = new RepositoryManager($readerMock);
        $manager->addType($typeName, $finderMock);
        $repository = $manager->getRepository($typeName);
        $this->assertInstanceOf('FOS\ElasticaBundle\Repository', $repository);
    }

    public function testThatGetRepositoryReturnsCustomRepository()
    {
        /** @var $finderMock \PHPUnit_Framework_MockObject_MockObject|\FOS\ElasticaBundle\Finder\TransformedFinder */
        $finderMock = $this->getMockBuilder('FOS\ElasticaBundle\Finder\TransformedFinder')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var $readerMock \PHPUnit_Framework_MockObject_MockObject|\Doctrine\Common\Annotations\Reader */
        $readerMock = $this->getMockBuilder('Doctrine\Common\Annotations\Reader')
            ->disableOriginalConstructor()
            ->getMock();

        $typeName = 'index/type';

        $manager = new RepositoryManager($readerMock);
        $manager->addType($typeName, $finderMock, 'FOS\ElasticaBundle\Tests\Manager\CustomRepository');
        $repository = $manager->getRepository($typeName);
        $this->assertInstanceOf('FOS\ElasticaBundle\Tests\Manager\CustomRepository', $repository);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testThatGetRepositoryThrowsExceptionIfEntityNotConfigured()
    {
        /** @var $finderMock \PHPUnit_Framework_MockObject_MockObject|\FOS\ElasticaBundle\Finder\TransformedFinder */
        $finderMock = $this->getMockBuilder('FOS\ElasticaBundle\Finder\TransformedFinder')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var $readerMock \PHPUnit_Framework_MockObject_MockObject|\Doctrine\Common\Annotations\Reader */
        $readerMock = $this->getMockBuilder('Doctrine\Common\Annotations\Reader')
            ->disableOriginalConstructor()
            ->getMock();

        $typeName = 'index/type';

        $manager = new RepositoryManager($readerMock);
        $manager->addType($typeName, $finderMock);
        $manager->getRepository('Missing type');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testThatGetRepositoryThrowsExceptionIfCustomRepositoryNotFound()
    {
        /** @var $finderMock \PHPUnit_Framework_MockObject_MockObject|\FOS\ElasticaBundle\Finder\TransformedFinder */
        $finderMock = $this->getMockBuilder('FOS\ElasticaBundle\Finder\TransformedFinder')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var $readerMock \PHPUnit_Framework_MockObject_MockObject|\Doctrine\Common\Annotations\Reader */
        $readerMock = $this->getMockBuilder('Doctrine\Common\Annotations\Reader')
            ->disableOriginalConstructor()
            ->getMock();

        $typeName = 'index/type';

        $manager = new RepositoryManager($readerMock);
        $manager->addType($typeName, $finderMock, 'FOS\ElasticaBundle\Tests\MissingRepository');
        $manager->getRepository($typeName);
    }
}
