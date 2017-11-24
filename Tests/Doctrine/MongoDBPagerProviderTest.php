<?php

namespace FOS\ElasticaBundle\Tests\Doctrine;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ODM\MongoDB\Query\Builder;
use FOS\ElasticaBundle\Doctrine\MongoDBPagerProvider;
use FOS\ElasticaBundle\Provider\PagerfantaPager;
use FOS\ElasticaBundle\Provider\PagerProviderInterface;
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

        new MongoDBPagerProvider($doctrine, $objectClass, $baseConfig);
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
            ->willReturn($manager);


        $doctrine = $this->createDoctrineMock();
        $doctrine
            ->expects($this->once())
            ->method('getManagerForClass')
            ->with($objectClass)
            ->willReturn($manager);

        $provider = new MongoDBPagerProvider($doctrine, $objectClass, $baseConfig);

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

        $expectedBuilder = $this->createMock(Builder::class);

        $repository = $this->createMock(DocumentRepository::class);
        $repository
            ->expects($this->once())
            ->method('createCustomQueryBuilder')
            ->willReturn($expectedBuilder);

        $manager = $this->createMock(DocumentManager::class);
        $manager
            ->expects($this->once())
            ->method('getRepository')
            ->willReturn($manager);


        $doctrine = $this->createDoctrineMock();
        $doctrine
            ->expects($this->once())
            ->method('getManagerForClass')
            ->willReturn($manager);

        $provider = new MongoDBPagerProvider($doctrine, $objectClass, $baseConfig);

        $pager = $provider->provide(['query_builder_method' => 'createCustomQueryBuilder']);

        $this->assertInstanceOf(PagerfantaPager::class, $pager);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ManagerRegistry
     */
    private function createDoctrineMock()
    {
        return $this->createMock(ManagerRegistry::class);
    }

}
