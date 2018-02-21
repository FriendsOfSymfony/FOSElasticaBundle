<?php

namespace FOS\ElasticaBundle\Tests\Unit\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\OrderBy;
use Doctrine\ORM\QueryBuilder;
use FOS\ElasticaBundle\Doctrine\ORMPagerProvider;
use FOS\ElasticaBundle\Doctrine\RegisterListenersService;
use FOS\ElasticaBundle\Provider\PagerfantaPager;
use FOS\ElasticaBundle\Provider\PagerInterface;
use FOS\ElasticaBundle\Provider\PagerProviderInterface;
use FOS\ElasticaBundle\Tests\Unit\Mocks\DoctrineORMCustomRepositoryMock;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Doctrine\ManagerRegistry;

class ORMPagerProviderTest extends TestCase
{
    public function testShouldImplementPagerProviderInterface()
    {
        $rc = new \ReflectionClass(ORMPagerProvider::class);

        $this->assertTrue($rc->implementsInterface(PagerProviderInterface::class));
    }

    public function testCouldBeConstructedWithExpectedArguments()
    {
        $doctrine = $this->createDoctrineMock();
        $objectClass = 'anObjectClass';
        $baseConfig = [];

        new ORMPagerProvider($doctrine, $this->createRegisterListenersServiceMock(), $objectClass, $baseConfig);
    }

    public function testShouldReturnPagerfantaPagerWithDoctrineORMAdapter()
    {
        $objectClass = 'anObjectClass';
        $baseConfig = ['query_builder_method' => 'createQueryBuilder'];

        $expectedBuilder = $this->createMock(QueryBuilder::class);
        $expectedBuilder->method('getDQLPart')
            ->with('orderBy')
            ->willReturn(array($this->createMock(OrderBy::class)));

        $repository = $this->createMock(EntityRepository::class);
        $repository
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($expectedBuilder);

        $manager = $this->createMock(EntityManager::class);
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

        $provider = new ORMPagerProvider($doctrine, $this->createRegisterListenersServiceMock(), $objectClass, $baseConfig);

        $pager = $provider->provide();

        $this->assertInstanceOf(PagerfantaPager::class, $pager);

        $adapter = $pager->getPagerfanta()->getAdapter();
        $this->assertInstanceOf(DoctrineORMAdapter::class, $adapter);
    }

    public function testShouldAllowCallCustomRepositoryMethod()
    {
        $objectClass = 'anObjectClass';
        $baseConfig = ['query_builder_method' => 'createQueryBuilder'];

        $expectedBuilder = $this->createMock(QueryBuilder::class);
        $expectedBuilder->method('getDQLPart')
            ->with('orderBy')
            ->willReturn(array($this->createMock(OrderBy::class)));

        $repository = $this->createMock(DoctrineORMCustomRepositoryMock::class);
        $repository
            ->expects($this->once())
            ->method('createCustomQueryBuilder')
            ->willReturn($expectedBuilder);

        $manager = $this->createMock(EntityManager::class);
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

        $provider = new ORMPagerProvider($doctrine, $this->createRegisterListenersServiceMock(), $objectClass, $baseConfig);

        $pager = $provider->provide(['query_builder_method' => 'createCustomQueryBuilder']);

        $this->assertInstanceOf(PagerfantaPager::class, $pager);
    }

    public function testShouldCallRegisterListenersService()
    {
        $objectClass = 'anObjectClass';
        $baseConfig = ['query_builder_method' => 'createQueryBuilder'];

        $expectedBuilder = $this->createMock(QueryBuilder::class);
        $expectedBuilder->method('getDQLPart')
            ->with('orderBy')
            ->willReturn(array($this->createMock(OrderBy::class)));

        $repository = $this->createMock(EntityRepository::class);
        $repository
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($expectedBuilder);

        $manager = $this->createMock(EntityManager::class);
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

        $registerListenersMock = $this->createRegisterListenersServiceMock();
        $registerListenersMock
            ->expects($this->once())
            ->method('register')
            ->with($this->identicalTo($manager), $this->isInstanceOf(PagerInterface::class), $baseConfig)
        ;

        $provider = new ORMPagerProvider($doctrine, $registerListenersMock, $objectClass, $baseConfig);

        $provider->provide();
    }

    /**
     * @return RegisterListenersService|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createRegisterListenersServiceMock()
    {
        return $this->createMock(RegisterListenersService::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ManagerRegistry
     */
    private function createDoctrineMock()
    {
        return $this->createMock(ManagerRegistry::class);
    }
}
