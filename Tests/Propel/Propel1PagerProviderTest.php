<?php
namespace FOS\ElasticaBundle\Tests\Propel;

use FOS\ElasticaBundle\Propel\Propel1PagerProvider;
use FOS\ElasticaBundle\Provider\PagerfantaPager;
use FOS\ElasticaBundle\Provider\PagerProviderInterface;
use FOS\ElasticaBundle\Tests\Mocks\PropelModelFooQuery;
use Pagerfanta\Adapter\PropelAdapter;

class Propel1PagerProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldImplementPagerProviderInterface()
    {
        $rc = new \ReflectionClass(Propel1PagerProvider::class);

        $this->assertTrue($rc->implementsInterface(PagerProviderInterface::class));
    }

    public function testCouldBeConstructedWithExpectedArguments()
    {
        $objectClass = 'anObjectClass';
        $baseConfig = [];

        new Propel1PagerProvider($objectClass, $baseConfig);
    }

    public function testShouldReturnPagerfanataPagerWithDoctrineODMMongoDBAdapter()
    {
        $objectClass = 'FOS\ElasticaBundle\Tests\Mocks\PropelModelFoo';
        $baseConfig = ['query_builder_method' => 'createQueryBuilder'];

        $provider = new Propel1PagerProvider($objectClass, $baseConfig);

        $pager = $provider->provide();

        $this->assertInstanceOf(PagerfantaPager::class, $pager);

        $adapter = $pager->getPagerfanta()->getAdapter();
        $this->assertInstanceOf(PropelAdapter::class, $adapter);

        $this->assertSame(PropelModelFooQuery::$latestCreatedInstance, $adapter->getQuery());
    }
}