<?php

namespace FOQ\ElasticaBundle\Tests\Doctrine;

class AbstractProviderTest extends \PHPUnit_Framework_TestCase
{
    private $objectClass;
    private $objectManager;
    private $objectPersister;
    private $options;
    private $managerRegistry;

    public function setUp()
    {
       if (!interface_exists('Doctrine\Common\Persistence\ManagerRegistry')) {
           $this->markTestSkipped('Doctrine Common is not available.');
       }

       $this->objectClass = 'objectClass';
       $this->options = array();

       $this->objectPersister = $this->getMockObjectPersister();
       $this->managerRegistry = $this->getMockManagerRegistry();
       $this->objectManager = $this->getMockObjectManager();

       $this->managerRegistry->expects($this->any())
           ->method('getManagerForClass')
           ->with($this->objectClass)
           ->will($this->returnValue($this->objectManager));
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

        $providerInvocationOffset = 2;

        foreach ($objectsByIteration as $i => $objects) {
            $offset = $objects[0] - 1;

            $provider->expects($this->at($providerInvocationOffset + $i))
                ->method('fetchSlice')
                ->with($queryBuilder, $batchSize, $offset)
                ->will($this->returnValue($objects));

            $this->objectPersister->expects($this->at($i))
                ->method('insertMany')
                ->with($objects);

            $this->objectManager->expects($this->at($i))
                ->method('clear');
        }

        $provider->populate();
    }

    public function providePopulateIterations()
    {
        return array(
            array(
                100,
                array(range(1,100)),
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

        $provider->expects($this->any())
            ->method('fetchSlice')
            ->will($this->returnValue($objects));

        $this->objectManager->expects($this->never())
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

        $provider->expects($this->any())
            ->method('fetchSlice')
            ->will($this->returnValue($objects));

        $loggerClosureInvoked = false;
        $loggerClosure = function () use (&$loggerClosureInvoked) {
            $loggerClosureInvoked = true;
        };

        $provider->populate();
        $this->assertFalse($loggerClosureInvoked);

        $provider->populate($loggerClosure);
        $this->assertTrue($loggerClosureInvoked);
    }

    /**
     * @return FOQ\ElasticaBundle\Doctrine\AbstractProvider
     */
    private function getMockAbstractProvider()
    {
        return $this->getMockForAbstractClass('FOQ\ElasticaBundle\Doctrine\AbstractProvider', array(
            $this->objectPersister,
            $this->objectClass,
            $this->options,
            $this->managerRegistry,
        ));
    }

    /**
     * @return Doctrine\Common\Persistence\ManagerRegistry
     */
    private function getMockManagerRegistry()
    {
        return $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
    }

    /**
     * @return FOQ\ElasticaBundle\Tests\Doctrine\ObjectManager
     */
    private function getMockObjectManager()
    {
        return $this->getMock(__NAMESPACE__ . '\ObjectManager');
    }

    /**
     * @return FOQ\ElasticaBundle\Persister\ObjectPersisterInterface
     */
    private function getMockObjectPersister()
    {
        return $this->getMock('FOQ\ElasticaBundle\Persister\ObjectPersisterInterface');
    }
}

/**
 * Doctrine\Common\Persistence\ObjectManager does not include a clear() method
 * in its interface, so create a new interface for mocking.
 */
interface ObjectManager
{
    function clear();
}
