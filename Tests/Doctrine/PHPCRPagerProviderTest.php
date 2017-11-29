<?php

namespace FOS\ElasticaBundle\Tests\Doctrine;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\DocumentRepository;
use Doctrine\ODM\PHPCR\Query\Builder;
use FOS\ElasticaBundle\Doctrine\PHPCRPagerProvider;
use FOS\ElasticaBundle\Provider\PagerfantaPager;
use FOS\ElasticaBundle\Provider\PagerProviderInterface;
use FOS\ElasticaBundle\Tests\Mocks\DoctrinePHPCRCustomRepositoryMock;
use Pagerfanta\Adapter\DoctrineODMPhpcrAdapter;

class PHPCRPagerProviderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!class_exists(DocumentManager::class)) {
            $this->markTestSkipped('Doctrine PHPCR is not present');
        }
    }

    public function testShouldImplementPagerProviderInterface()
    {
        $rc = new \ReflectionClass(PHPCRPagerProvider::class);

        $this->assertTrue($rc->implementsInterface(PagerProviderInterface::class));
    }

    public function testCouldBeConstructedWithExpectedArguments()
    {
        $doctrine = $this->createDoctrineMock();
        $objectClass = 'anObjectClass';
        $baseConfig = [];

        new PHPCRPagerProvider($doctrine, $objectClass, $baseConfig);
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

        $provider = new PHPCRPagerProvider($doctrine, $objectClass, $baseConfig);

        $pager = $provider->provide();

        $this->assertInstanceOf(PagerfantaPager::class, $pager);

        $adapter = $pager->getPagerfanta()->getAdapter();
        $this->assertInstanceOf(DoctrineODMPhpcrAdapter::class, $adapter);

        $this->assertAttributeSame($expectedBuilder, 'queryBuilder', $adapter);
    }

    public function testShouldAllowCallCustomRepositoryMethod()
    {
        $objectClass = 'anObjectClass';
        $baseConfig = ['query_builder_method' => 'createQueryBuilder'];

        $repository = $this->createMock(DoctrinePHPCRCustomRepositoryMock::class);
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

        $provider = new PHPCRPagerProvider($doctrine, $objectClass, $baseConfig);

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
