<?php

namespace FOS\ElasticaBundle\Tests\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use FOS\ElasticaBundle\Doctrine\ORMPagerProvider;
use FOS\ElasticaBundle\Provider\PagerfantaPager;
use FOS\ElasticaBundle\Provider\PagerProviderInterface;
use FOS\ElasticaBundle\Tests\Mocks\DoctrineORMCustomRepositoryMock;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Symfony\Bridge\Doctrine\ManagerRegistry;

class ORMPagerProviderTest extends \PHPUnit_Framework_TestCase
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

        new ORMPagerProvider($doctrine, $objectClass, $baseConfig);
    }

    public function testShouldReturnPagerfanataPagerWithDoctrineODMMongoDBAdapter()
    {
        $objectClass = 'anObjectClass';
        $baseConfig = ['query_builder_method' => 'createQueryBuilder'];

        $expectedBuilder = $this->getMock(QueryBuilder::class, [], [], '', false);

        $repository = $this->getMock(EntityRepository::class, [], [], '', false);
        $repository
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($expectedBuilder);

        $manager = $this->getMock(EntityManager::class, [], [], '', false);
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

        $provider = new ORMPagerProvider($doctrine, $objectClass, $baseConfig);

        $pager = $provider->provide();

        $this->assertInstanceOf(PagerfantaPager::class, $pager);

        $adapter = $pager->getPagerfanta()->getAdapter();
        $this->assertInstanceOf(DoctrineORMAdapter::class, $adapter);
    }

    public function testShouldAllowCallCustomRepositoryMethod()
    {
        $objectClass = 'anObjectClass';
        $baseConfig = ['query_builder_method' => 'createQueryBuilder'];

        $repository = $this->getMock(DoctrineORMCustomRepositoryMock::class, [], [], '', false);
        $repository
            ->expects($this->once())
            ->method('createCustomQueryBuilder')
            ->willReturn($this->getMock(QueryBuilder::class, [], [], '', false));

        $manager = $this->getMock(EntityManager::class, [], [], '', false);
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

        $provider = new ORMPagerProvider($doctrine, $objectClass, $baseConfig);

        $pager = $provider->provide(['query_builder_method' => 'createCustomQueryBuilder']);

        $this->assertInstanceOf(PagerfantaPager::class, $pager);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ManagerRegistry
     */
    private function createDoctrineMock()
    {
        return $this->getMock(ManagerRegistry::class, [], [], '', false);
    }
}
