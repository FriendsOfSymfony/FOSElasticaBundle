<?php

namespace FOS\ElasticaBundle\Tests\Unit\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use FOS\ElasticaBundle\Doctrine\RegisterListenersService;
use FOS\ElasticaBundle\Persister\Event\Events;
use FOS\ElasticaBundle\Persister\Event\PostInsertObjectsEvent;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Provider\PagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\Event as LegacyEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as LegacyEventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class RegisterListenersServiceTest extends TestCase
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

        $this->dispatch(
            $dispatcher,
            new PostInsertObjectsEvent($pager, $this->createObjectPersisterMock(), [], []),
            Events::POST_INSERT_OBJECTS
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

        $this->dispatch(
            $dispatcher,
            new PostInsertObjectsEvent($pager, $this->createObjectPersisterMock(), [], []),
            Events::POST_INSERT_OBJECTS
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

        $this->dispatch(
            $dispatcher,
            new PostInsertObjectsEvent($anotherPager, $this->createObjectPersisterMock(), [], []),
            Events::POST_INSERT_OBJECTS
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
        $this->dispatch(
            $dispatcher,
            new PostInsertObjectsEvent($pager, $this->createObjectPersisterMock(), [], []),
            Events::POST_INSERT_OBJECTS
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
        $this->dispatch(
            $dispatcher,
            new PostInsertObjectsEvent($anotherPager, $this->createObjectPersisterMock(), [], []),
            Events::POST_INSERT_OBJECTS
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

        $configuration = $this->createMock(Configuration::class);
        $connection = $this->createMock(Connection::class);
        $connection
            ->expects($this->once())
            ->method('getConfiguration')
            ->willReturn($configuration)
        ;

        $manager = $this->createMock(EntityManagerInterface::class);
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

        $manager = $this->createMock(EntityManagerInterface::class);
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

        $configuration = $this->createMock(\Doctrine\MongoDB\Configuration::class);
        $connection = $this->createMock(\Doctrine\MongoDB\Connection::class);
        $connection
            ->expects($this->once())
            ->method('getConfiguration')
            ->willReturn($configuration)
        ;

        $manager = $this->createMock(\Doctrine\ODM\MongoDB\DocumentManager::class);
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

        $manager = $this->createMock(\Doctrine\ODM\MongoDB\DocumentManager::class);
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

        $manager = $this->createMock(\Doctrine\ODM\PHPCR\DocumentManagerInterface::class);

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
        return $this->createMock(PagerInterface::class);
    }

    /**
     * @return ObjectPersisterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createObjectPersisterMock()
    {
        return $this->createMock(ObjectPersisterInterface::class);
    }

    /**
     * @return ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createObjectManagerMock()
    {
        return $this->createMock(ObjectManager::class);
    }

    /**
     * @return EventDispatcher
     */
    private function createDispatcher()
    {
        return new EventDispatcher();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|EventDispatcherInterface|LegacyEventDispatchernterface
     */
    private function createDispatcherMock()
    {
        return $this->createMock(EventDispatcher::class);
    }

    /**
     * @param EventDispatcherInterface|LegacyEventDispatcherInterface $dispatcher
     * @param Event|LegacyEvent                                       $event
     * @param string                                                  $eventName
     */
    private function dispatch($dispatcher, $event, $eventName): void
    {
        if ($dispatcher instanceof EventDispatcherInterface) {
            // Symfony >= 4.3
            $dispatcher->dispatch($event, $eventName);
        } else {
            // Symfony 3.4
            $dispatcher->dispatch($eventName, $event);
        }
    }
}
