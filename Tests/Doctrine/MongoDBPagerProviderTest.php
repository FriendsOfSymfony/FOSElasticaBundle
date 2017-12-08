<?php

namespace FOS\ElasticaBundle\Tests\Doctrine;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ODM\MongoDB\Query\Builder;
use FOS\ElasticaBundle\Doctrine\MongoDBPagerProvider;
use FOS\ElasticaBundle\Doctrine\RegisterListenersService;
use FOS\ElasticaBundle\Provider\PagerfantaPager;
use FOS\ElasticaBundle\Provider\PagerInterface;
use FOS\ElasticaBundle\Provider\PagerProviderInterface;
use FOS\ElasticaBundle\Tests\Mocks\DoctrineMongoDBCustomRepositoryMock;
use Pagerfanta\Adapter\DoctrineODMMongoDBAdapter;
use Symfony\Bridge\Doctrine\ManagerRegistry;

class MongoDBPagerProviderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!class_exists(DocumentManager::class)) {
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

        $expectedBuilder = $this->getMock(Builder::class, [], [], '', false);

        $repository = $this->getMock(DocumentRepository::class, [], [], '', false);
        $repository
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($expectedBuilder);

        $manager = $this->getMock(DocumentManager::class, [], [], '', false);
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

        $this->assertAttributeSame($expectedBuilder, 'queryBuilder', $adapter);
    }

    public function testShouldAllowCallCustomRepositoryMethod()
    {
        $objectClass = 'anObjectClass';
        $baseConfig = ['query_builder_method' => 'createQueryBuilder'];

        $repository = $this->getMock(DoctrineMongoDBCustomRepositoryMock::class, [], [], '', false);
        $repository
            ->expects($this->once())
            ->method('createCustomQueryBuilder')
            ->willReturn($this->getMock(Builder::class, [], [], '', false));

        $manager = $this->getMock(DocumentManager::class, [], [], '', false);
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

        $repository = $this->getMock(DoctrineMongoDBCustomRepositoryMock::class, [], [], '', false);
        $repository
            ->expects($this->once())
            ->method('createCustomQueryBuilder')
            ->willReturn($this->getMock(Builder::class, [], [], '', false));

        $manager = $this->getMock(DocumentManager::class, [], [], '', false);
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

        $provider = new MongoDBPagerProvider($doctrine,$registerListenersMock, $objectClass, $baseConfig);

        $provider->provide();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ManagerRegistry
     */
    private function createDoctrineMock()
    {
        return $this->getMock(ManagerRegistry::class, [], [], '', false);
    }

    /**
     * @return RegisterListenersService|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createRegisterListenersServiceMock()
    {
        return $this->getMock(RegisterListenersService::class, [], [], '', false);
    }
}