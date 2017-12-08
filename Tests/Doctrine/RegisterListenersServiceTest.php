<?php
namespace FOS\ElasticaBundle\Tests\Doctrine;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use FOS\ElasticaBundle\Doctrine\RegisterListenersService;
use FOS\ElasticaBundle\Persister\Event\Events;
use FOS\ElasticaBundle\Persister\Event\PostInsertObjectsEvent;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Provider\PagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RegisterListenersServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testCouldBeConstructedWithDispatcherArgument()
    {
        new RegisterListenersService($this->createDispatcherMock());
    }

    public function testShouldRegisterClearObjectManagerListenerByDefaultAndDispatchOnPostPersistEvent()
    {
        $dispatcher = $this->createDispatcher();

        $service = new RegisterListenersService($dispatcher);

        $manager = $this->createObjectManagerMock();
        $manager
            ->expects($this->once())
            ->method('clear')
        ;

        $pager = $this->createPagerMock();

        $service->register($manager, $pager, []);

        $dispatcher->dispatch(
            Events::POST_INSERT_OBJECTS,
            new PostInsertObjectsEvent($pager, $this->createObjectPersisterMock(), [], [])
        );
    }

    public function testShouldNotRegisterClearObjectManagerListenerIfOptionFalse()
    {
        $dispatcher = $this->createDispatcher();

        $service = new RegisterListenersService($dispatcher);

        $manager = $this->createObjectManagerMock();
        $manager
            ->expects($this->never())
            ->method('clear')
        ;

        $pager = $this->createPagerMock();

        $service->register($manager, $pager, [
            'clear_object_manager' => false
        ]);

        $dispatcher->dispatch(
            Events::POST_INSERT_OBJECTS,
            new PostInsertObjectsEvent($pager, $this->createObjectPersisterMock(), [], [])
        );
    }

    public function testShouldNotCallClearObjectManagerListenerForAnotherPagers()
    {
        $dispatcher = $this->createDispatcher();

        $service = new RegisterListenersService($dispatcher);

        $manager = $this->createObjectManagerMock();
        $manager
            ->expects($this->never())
            ->method('clear')
        ;

        $pager = $this->createPagerMock();
        $anotherPager = $this->createPagerMock();

        $service->register($manager, $pager, [
            'clear_object_manager' => true
        ]);

        $dispatcher->dispatch(
            Events::POST_INSERT_OBJECTS,
            new PostInsertObjectsEvent($anotherPager, $this->createObjectPersisterMock(), [], [])
        );
    }

    public function testShouldNotRegisterSleepListenerByDefault()
    {
        $dispatcher = $this->createDispatcherMock();
        $dispatcher
            ->expects($this->never())
            ->method('addListener')
            ->with(Events::POST_INSERT_OBJECTS, $this->isInstanceOf(\Closure::class))
        ;

        $service = new RegisterListenersService($dispatcher);

        $manager = $this->createObjectManagerMock();

        $pager = $this->createPagerMock();

        $service->register($manager, $pager, [
            'clear_object_manager' => false,
        ]);
    }

    public function testShouldRegisterSleepListenerIfOptionNotZero()
    {
        $dispatcher = $this->createDispatcher();

        $service = new RegisterListenersService($dispatcher);

        $manager = $this->createObjectManagerMock();

        $pager = $this->createPagerMock();

        $service->register($manager, $pager, [
            'clear_object_manager' => false,
            'sleep' => 2000000,
        ]);

        $time = microtime(true);
        $dispatcher->dispatch(
            Events::POST_INSERT_OBJECTS,
            new PostInsertObjectsEvent($pager, $this->createObjectPersisterMock(), [], [])
        );

        $this->assertGreaterThan(1.5, microtime(true) - $time);
    }

    public function testShouldNotCallSleepListenerForAnotherPagers()
    {
        $dispatcher = $this->createDispatcher();

        $service = new RegisterListenersService($dispatcher);

        $manager = $this->createObjectManagerMock();

        $pager = $this->createPagerMock();
        $anotherPager = $this->createPagerMock();

        $service->register($manager, $pager, [
            'clear_object_manager' => false,
            'sleep' => 2000000,
        ]);

        $time = microtime(true);
        $dispatcher->dispatch(
            Events::POST_INSERT_OBJECTS,
            new PostInsertObjectsEvent($anotherPager, $this->createObjectPersisterMock(), [], [])
        );

        $this->assertLessThan(1, microtime(true) - $time);
    }

    public function testShouldRegisterDisableDebugLoggingByDefaultForEntityManager()
    {
        $dispatcher = $this->createDispatcherMock();
        $dispatcher
            ->expects($this->at(0))
            ->method('addListener')
            ->with(Events::PRE_FETCH_OBJECTS, $this->isInstanceOf(\Closure::class));
        $dispatcher
            ->expects($this->at(1))
            ->method('addListener')
            ->with(Events::PRE_INSERT_OBJECTS, $this->isInstanceOf(\Closure::class));

        $service = new RegisterListenersService($dispatcher);

        $configuration = $this->getMock(Configuration::class);
        $connection = $this->getMock(Connection::class, [], [], '', false);
        $connection
            ->expects($this->once())
            ->method('getConfiguration')
            ->willReturn($configuration)
        ;

        $manager = $this->getMock(EntityManagerInterface::class);
        $manager
            ->expects($this->once())
            ->method('getConnection')
            ->willReturn($connection)
        ;


        $pager = $this->createPagerMock();

        $service->register($manager, $pager, [
            'clear_object_manager' => false,
            'sleep' => 0,
        ]);
    }

    public function testShouldNotRegisterDisableDebugLoggingIfOptionTrueForEntityManager()
    {
        $dispatcher = $this->createDispatcherMock();
        $dispatcher
            ->expects($this->never())
            ->method('addListener')
        ;

        $service = new RegisterListenersService($dispatcher);

        $manager = $this->getMock(EntityManagerInterface::class);
        $manager
            ->expects($this->never())
            ->method('getConnection')
        ;


        $pager = $this->createPagerMock();

        $service->register($manager, $pager, [
            'clear_object_manager' => false,
            'sleep' => 0,
            'debug_logging' => true,
        ]);
    }

    public function testShouldRegisterDisableDebugLoggingByDefaultForMongoDBDocumentManager()
    {
        if (!class_exists(\Doctrine\ODM\MongoDB\DocumentManager::class)) {
            $this->markTestSkipped('Doctrine MongoDB ODM is not available.');
        }

        $dispatcher = $this->createDispatcherMock();
        $dispatcher
            ->expects($this->at(0))
            ->method('addListener')
            ->with(Events::PRE_FETCH_OBJECTS, $this->isInstanceOf(\Closure::class));
        $dispatcher
            ->expects($this->at(1))
            ->method('addListener')
            ->with(Events::PRE_INSERT_OBJECTS, $this->isInstanceOf(\Closure::class));

        $service = new RegisterListenersService($dispatcher);

        $configuration = $this->getMock(Configuration::class);
        $connection = $this->getMock(Connection::class, [], [], '', false);
        $connection
            ->expects($this->once())
            ->method('getConfiguration')
            ->willReturn($configuration)
        ;

        $manager = $this->getMock(\Doctrine\ODM\MongoDB\DocumentManager::class, [], [], '', false);
        $manager
            ->expects($this->once())
            ->method('getConnection')
            ->willReturn($connection)
        ;


        $pager = $this->createPagerMock();

        $service->register($manager, $pager, [
            'clear_object_manager' => false,
            'sleep' => 0,
        ]);
    }

    public function testShouldNotRegisterDisableDebugLoggingIfOptionTrueForMongoDBDocumentManager()
    {
        if (!class_exists(\Doctrine\ODM\MongoDB\DocumentManager::class)) {
            $this->markTestSkipped('Doctrine MongoDB ODM is not available.');
        }

        $dispatcher = $this->createDispatcherMock();
        $dispatcher
            ->expects($this->never())
            ->method('addListener')
        ;

        $service = new RegisterListenersService($dispatcher);

        $manager = $this->getMock(\Doctrine\ODM\MongoDB\DocumentManager::class, [], [], '', false);
        $manager
            ->expects($this->never())
            ->method('getConnection')
        ;


        $pager = $this->createPagerMock();

        $service->register($manager, $pager, [
            'clear_object_manager' => false,
            'sleep' => 0,
            'debug_logging' => true,
        ]);
    }

    public function testShouldIgnoreDebugLoggingOptionForPHPCRManager()
    {
        if (!class_exists(\Doctrine\ODM\PHPCR\DocumentManagerInterface::class)) {
            $this->markTestSkipped('Doctrine PHPCR is not present');
        }

        $dispatcher = $this->createDispatcherMock();
        $dispatcher
            ->expects($this->never())
            ->method('addListener')
        ;

        $service = new RegisterListenersService($dispatcher);

        $manager = $this->getMock(\Doctrine\ODM\PHPCR\DocumentManagerInterface::class);

        $pager = $this->createPagerMock();

        $service->register($manager, $pager, [
            'clear_object_manager' => false,
            'sleep' => 0,
        ]);

        $service->register($manager, $pager, [
            'clear_object_manager' => false,
            'sleep' => 0,
            'debug_logging' => false,
        ]);
    }

    private function createPagerMock()
    {
        return $this->getMock(PagerInterface::class, [], [], '', false);
    }

    /**
     * @return ObjectPersisterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createObjectPersisterMock()
    {
        return $this->getMock(ObjectPersisterInterface::class, [], [], '', false);
    }

    /**
     * @return ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createObjectManagerMock()
    {
        return $this->getMock(ObjectManager::class, [], [], '', false);
    }

    /**
     * @return EventDispatcher
     */
    private function createDispatcher()
    {
        return new EventDispatcher();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|EventDispatcherInterface
     */
    private function createDispatcherMock()
    {
        return $this->getMock(EventDispatcherInterface::class, [], [], '', false);
    }
}
