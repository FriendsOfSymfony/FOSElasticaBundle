<?php

namespace FOS\ElasticaBundle\Tests\Doctrine;

use Elastica\Bulk\ResponseSet;
use Elastica\Response;

class AbstractProviderTest extends \PHPUnit_Framework_TestCase
{
    private $objectClass;
    private $objectManager;
    private $objectPersister;
    private $options;
    private $managerRegistry;
    private $indexable;
    private $sliceFetcher;

    public function setUp()
    {
        $this->objectClass = 'objectClass';
        $this->options = array('debug_logging' => true, 'indexName' => 'index', 'typeName' => 'type');
<
        $this->objectPersister = $this->getMockObjectPersister();
        $this->managerRegistry = $this->getMockManagerRegistry();
        $this->objectManager = $this->getMockObjectManager();
        $this->indexable = $this->getMockIndexable();

        $this->managerRegistry->expects($this->any())
            ->method('getManagerForClass')
            ->with($this->objectClass)
            ->will($this->returnValue($this->objectManager));

        $this->sliceFetcher = $this->getMockSliceFetcher();
    }

    /**
     * @dataProvider providePopulateIterations
     */
    public function testPopulateIterations($nbObjects, $objectsByIteration, $batchSize)
    {
        $this->options['batch_size'] = $batchSize;

        $provider = $this->getMockAbstractProvider();

        $queryBuilder = new \stdClass();

        $provider->expects($this->once())
            ->method('createQueryBuilder')
            ->will($this->returnValue($queryBuilder));

        $provider->expects($this->once())
            ->method('countObjects')
            ->with($queryBuilder)
            ->will($this->returnValue($nbObjects));

        $this->indexable->expects($this->any())
            ->method('isObjectIndexable')
            ->with('index', 'type', $this->anything())
            ->will($this->returnValue(true));

        $providerInvocationOffset = 2;
        $previousSlice = array();

        foreach ($objectsByIteration as $i => $objects) {
            $offset = $objects[0] - 1;

            $this->sliceFetcher->expects($this->at($i))
                ->method('fetch')
                ->with($queryBuilder, $batchSize, $offset, $previousSlice, array('id'))
                ->will($this->returnValue($objects));

            $this->objectManager->expects($this->at($i))
                ->method('clear');

            $previousSlice = $objects;
        }

        $this->objectPersister->expects($this->exactly(count($objectsByIteration)))
            ->method('insertMany');

        $provider->populate();
    }

    /**
     * @dataProvider providePopulateIterations
     */
    public function testPopulateIterationsWithoutSliceFetcher($nbObjects, $objectsByIteration, $batchSize)
    {
        $this->options['batch_size'] = $batchSize;

        $provider = $this->getMockAbstractProvider(false);

        $queryBuilder = new \stdClass();

        $provider->expects($this->once())
            ->method('createQueryBuilder')
            ->will($this->returnValue($queryBuilder));

        $provider->expects($this->once())
            ->method('countObjects')
            ->with($queryBuilder)
            ->will($this->returnValue($nbObjects));

        $this->indexable->expects($this->any())
            ->method('isObjectIndexable')
            ->with('index', 'type', $this->anything())
            ->will($this->returnValue(true));

        $providerInvocationOffset = 2;

        foreach ($objectsByIteration as $i => $objects) {
            $offset = $objects[0] - 1;

            $provider->expects($this->at($providerInvocationOffset + $i))
                ->method('fetchSlice')
                ->with($queryBuilder, $batchSize, $offset)
                ->will($this->returnValue($objects));

            $this->objectManager->expects($this->at($i))
                ->method('clear');
        }

        $this->objectPersister->expects($this->exactly(count($objectsByIteration)))
            ->method('insertMany');

        $provider->populate();
    }

    public function providePopulateIterations()
    {
        return array(
            array(
                100,
                array(range(1, 100)),
                100,
            ),
            array(
                105,
                array(range(1, 50), range(51, 100), range(101, 105)),
                50,
            ),
        );
    }

    public function testPopulateShouldNotClearObjectManager()
    {
        $nbObjects = 1;
        $objects = array(1);
        $this->options['clear_object_manager'] = false;

        $provider = $this->getMockAbstractProvider();

        $provider->expects($this->any())
            ->method('countObjects')
            ->will($this->returnValue($nbObjects));

        $this->sliceFetcher->expects($this->any())
            ->method('fetch')
            ->will($this->returnValue($objects));

        $this->indexable->expects($this->any())
            ->method('isObjectIndexable')
            ->with('index', 'type', $this->anything())
            ->will($this->returnValue(true));

        $this->objectManager->expects($this->never())
            ->method('clear');

        $provider->populate();
    }

    public function testPopulateShouldClearObjectManagerForFilteredBatch()
    {
        $nbObjects = 1;
        $objects = array(1);

        $provider = $this->getMockAbstractProvider(true);

        $provider->expects($this->any())
            ->method('countObjects')
            ->will($this->returnValue($nbObjects));

        $this->sliceFetcher->expects($this->any())
            ->method('fetch')
            ->will($this->returnValue($objects));

        $this->indexable->expects($this->any())
            ->method('isObjectIndexable')
            ->with('index', 'type', $this->anything())
            ->will($this->returnValue(false));

        $this->objectManager->expects($this->once())
            ->method('clear');

        $provider->populate();
    }

    public function testPopulateInvokesLoggerClosure()
    {
        $nbObjects = 1;
        $objects = array(1);

        $provider = $this->getMockAbstractProvider();

        $provider->expects($this->any())
            ->method('countObjects')
            ->will($this->returnValue($nbObjects));

        $this->sliceFetcher->expects($this->any())
            ->method('fetch')
            ->will($this->returnValue($objects));

        $this->indexable->expects($this->any())
            ->method('isObjectIndexable')
            ->with('index', 'type', $this->anything())
            ->will($this->returnValue(true));

        $loggerClosureInvoked = false;
        $loggerClosure = function () use (&$loggerClosureInvoked) {
            $loggerClosureInvoked = true;
        };

        $provider->populate();
        $this->assertFalse($loggerClosureInvoked);

        $provider->populate($loggerClosure);
        $this->assertTrue($loggerClosureInvoked);
    }

    public function testPopulateNotStopOnError()
    {
        $nbObjects = 1;
        $objects = array(1);

        $provider = $this->getMockAbstractProvider();

        $provider->expects($this->any())
            ->method('countObjects')
            ->will($this->returnValue($nbObjects));

        $this->sliceFetcher->expects($this->any())
            ->method('fetch')
            ->will($this->returnValue($objects));

        $this->indexable->expects($this->any())
            ->method('isObjectIndexable')
            ->with('index', 'type', $this->anything())
            ->will($this->returnValue(true));

        $this->objectPersister->expects($this->any())
            ->method('insertMany')
            ->will($this->throwException($this->getMockBulkResponseException()));

        $this->setExpectedException('Elastica\Exception\Bulk\ResponseException');

        $provider->populate(null, array('ignore-errors' => false));
    }

    public function testPopulateRunsIndexCallable()
    {
        $nbObjects = 2;
        $objects = array(1, 2);

        $provider = $this->getMockAbstractProvider();
        $provider->expects($this->any())
            ->method('countObjects')
            ->will($this->returnValue($nbObjects));

        $this->sliceFetcher->expects($this->any())
            ->method('fetch')
            ->will($this->returnValue($objects));

        $this->indexable->expects($this->at(0))
            ->method('isObjectIndexable')
            ->with('index', 'type', 1)
            ->will($this->returnValue(false));
        $this->indexable->expects($this->at(1))
            ->method('isObjectIndexable')
            ->with('index', 'type', 2)
            ->will($this->returnValue(true));

        $this->objectPersister->expects($this->once())
            ->method('insertMany')
            ->with(array(1 => 2));

        $provider->populate();
    }

    /**
     * @param boolean $setSliceFetcher Whether or not to set the slice fetcher.
     *
     * @return \FOS\ElasticaBundle\Doctrine\AbstractProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockAbstractProvider($setSliceFetcher = true)
    {
        return $this->getMockForAbstractClass('FOS\ElasticaBundle\Doctrine\AbstractProvider', array(
            $this->objectPersister,
            $this->indexable,
            $this->objectClass,
            $this->options,
            $this->managerRegistry,
            $setSliceFetcher ? $this->sliceFetcher : null
        ));
    }

    /**
     * @return \Elastica\Exception\Bulk\ResponseException
     */
    private function getMockBulkResponseException()
    {
        return $this->getMock('Elastica\Exception\Bulk\ResponseException', null, array(
            new ResponseSet(new Response(array()), array()),
        ));
    }

    /**
     * @return \Doctrine\Common\Persistence\ManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockManagerRegistry()
    {
        return $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
    }

    /**
     * @return ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockObjectManager()
    {
        $mock = $this->getMock(__NAMESPACE__.'\ObjectManager');

        $mock->expects($this->any())
            ->method('getClassMetadata')
            ->will($this->returnSelf());

        $mock->expects($this->any())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(array('id')));

        return $mock;
    }

    /**
     * @return \FOS\ElasticaBundle\Persister\ObjectPersisterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockObjectPersister()
    {
        return $this->getMock('FOS\ElasticaBundle\Persister\ObjectPersisterInterface');
    }

    /**
     * @return \FOS\ElasticaBundle\Provider\IndexableInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockIndexable()
    {
        return $this->getMock('FOS\ElasticaBundle\Provider\IndexableInterface');
    }

    /**
     * @return \FOS\ElasticaBundle\Doctrine\SliceFetcherInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockSliceFetcher()
    {
        return $this->getMock('FOS\ElasticaBundle\Doctrine\SliceFetcherInterface');
    }
}

/**
 * Doctrine\Common\Persistence\ObjectManager does not include a clear() method
 * in its interface, so create a new interface for mocking.
 */
interface ObjectManager
{
    public function clear();
    public function getClassMetadata();
    public function getIdentifierFieldNames();
}
