<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Doctrine;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use FOS\ElasticaBundle\Doctrine\MongoDBPagerProvider;
use FOS\ElasticaBundle\Doctrine\RegisterListenersService;
use FOS\ElasticaBundle\Provider\PagerfantaPager;
use FOS\ElasticaBundle\Provider\PagerInterface;
use FOS\ElasticaBundle\Provider\PagerProviderInterface;
use FOS\ElasticaBundle\Tests\Unit\Mocks\DoctrineMongoDBCustomRepositoryMock;
use Pagerfanta\Adapter\DoctrineODMMongoDBAdapter;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Doctrine\ManagerRegistry;

class MongoDBPagerProviderTest extends TestCase
{
    protected function setUp(): void
    {
        if (!\class_exists(DocumentManager::class)) {
            $this->markTestSkipped('Doctrine MongoDB ODM is not available.');
        }
    }

    public function testShouldImplementPagerProviderInterface()
    {
        $rc = new \ReflectionClass(MongoDBPagerProvider::class);

        $this->assertTrue($rc->implementsInterface(PagerProviderInterface::class));
    }

    public function testCouldBeConstructedWithExpectedArguments()
    {
        $doctrine = $this->createDoctrineMock();
        $objectClass = 'anObjectClass';
        $baseConfig = [];

        new MongoDBPagerProvider($doctrine, $this->createRegisterListenersServiceMock(), $objectClass, $baseConfig);
    }

    public function testShouldReturnPagerfanataPagerWithDoctrineODMMongoDBAdapter()
    {
        $objectClass = 'anObjectClass';
        $baseConfig = ['query_builder_method' => 'createQueryBuilder'];

        $expectedBuilder = $this->createMock(Builder::class);

        $repository = $this->createMock(DocumentRepository::class);
        $repository
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($expectedBuilder);

        $manager = $this->createMock(DocumentManager::class);
        $manager
            ->expects($this->once())
            ->method('getRepository')
            ->with($objectClass)
            ->willReturn($repository);

        $doctrine = $this->createDoctrineMock();
        $doctrine
            ->expects($this->once())
            ->method('getManagerForClass')
            ->with($objectClass)
            ->willReturn($manager);

        $provider = new MongoDBPagerProvider($doctrine, $this->createRegisterListenersServiceMock(), $objectClass, $baseConfig);

        $pager = $provider->provide();

        $this->assertInstanceOf(PagerfantaPager::class, $pager);

        $adapter = $pager->getPagerfanta()->getAdapter();
        $this->assertInstanceOf(DoctrineODMMongoDBAdapter::class, $adapter);
        $this->assertSame($expectedBuilder, $adapter->getQueryBuilder());
    }

    public function testShouldAllowCallCustomRepositoryMethod()
    {
        $objectClass = 'anObjectClass';
        $baseConfig = ['query_builder_method' => 'createQueryBuilder'];

        $repository = $this->createMock(DoctrineMongoDBCustomRepositoryMock::class);
        $repository
            ->expects($this->once())
            ->method('createCustomQueryBuilder')
            ->willReturn($this->createMock(Builder::class));

        $manager = $this->createMock(DocumentManager::class);
        $manager
            ->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository);

        $doctrine = $this->createDoctrineMock();
        $doctrine
            ->expects($this->once())
            ->method('getManagerForClass')
            ->willReturn($manager);

        $provider = new MongoDBPagerProvider($doctrine, $this->createRegisterListenersServiceMock(), $objectClass, $baseConfig);

        $pager = $provider->provide(['query_builder_method' => 'createCustomQueryBuilder']);

        $this->assertInstanceOf(PagerfantaPager::class, $pager);
    }

    public function testShouldCallRegisterListenersService()
    {
        $objectClass = 'anObjectClass';
        $baseConfig = ['query_builder_method' => 'createQueryBuilder'];

        $repository = $this->createMock(DocumentRepository::class);
        $repository
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($this->createMock(Builder::class));

        $manager = $this->createMock(DocumentManager::class);
        $manager
            ->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository);

        $doctrine = $this->createDoctrineMock();
        $doctrine
            ->expects($this->once())
            ->method('getManagerForClass')
            ->willReturn($manager);

        $registerListenersMock = $this->createRegisterListenersServiceMock();
        $registerListenersMock
            ->expects($this->once())
            ->method('register')
            ->with($this->identicalTo($manager), $this->isInstanceOf(PagerInterface::class), $baseConfig)
        ;

        $provider = new MongoDBPagerProvider($doctrine, $registerListenersMock, $objectClass, $baseConfig);

        $provider->provide();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ManagerRegistry
     */
    private function createDoctrineMock()
    {
        return $this->createMock(ManagerRegistry::class);
    }

    /**
     * @return RegisterListenersService|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createRegisterListenersServiceMock()
    {
        return $this->createMock(RegisterListenersService::class);
    }
}
