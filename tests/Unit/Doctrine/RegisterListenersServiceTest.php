<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use FOS\ElasticaBundle\Doctrine\RegisterListenersService;
use FOS\ElasticaBundle\Persister\Event\PostInsertObjectsEvent;
use FOS\ElasticaBundle\Persister\Event\PreFetchObjectsEvent;
use FOS\ElasticaBundle\Persister\Event\PreInsertObjectsEvent;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Provider\PagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
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

        $dispatcher->dispatch(
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
            'clear_object_manager' => false,
        ]);

        $dispatcher->dispatch(
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
            'clear_object_manager' => true,
        ]);

        $dispatcher->dispatch(
            new PostInsertObjectsEvent($anotherPager, $this->createObjectPersisterMock(), [], [])
        );
    }

    public function testShouldNotRegisterSleepListenerByDefault()
    {
        $dispatcher = $this->createDispatcherMock();
        $dispatcher
            ->expects($this->never())
            ->method('addListener')
            ->with(PostInsertObjectsEvent::class, $this->isInstanceOf(\Closure::class))
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

        $time = \microtime(true);
        $dispatcher->dispatch(
            new PostInsertObjectsEvent($pager, $this->createObjectPersisterMock(), [], [])
        );

        $this->assertGreaterThan(1.5, \microtime(true) - $time);
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

        $time = \microtime(true);
        $dispatcher->dispatch(
            new PostInsertObjectsEvent($anotherPager, $this->createObjectPersisterMock(), [], [])
        );

        $this->assertLessThan(1, \microtime(true) - $time);
    }

    public function testShouldRegisterDisableDebugLoggingByDefaultForEntityManager()
    {
        $dispatcher = $this->createDispatcherMock();
        $dispatcher->expects($this->exactly(2))
            ->method('addListener')
            ->withConsecutive(
                [PreFetchObjectsEvent::class, $this->isInstanceOf(\Closure::class)],
                [PreInsertObjectsEvent::class, $this->isInstanceOf(\Closure::class)]
            )
        ;

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

    public function testShouldIgnoreDebugLoggingOptionForMongoDBDocumentManager()
    {
        if (!\class_exists(\Doctrine\ODM\MongoDB\DocumentManager::class)) {
            $this->markTestSkipped('Doctrine MongoDB ODM is not available.');
        }

        $dispatcher = $this->createDispatcherMock();
        $dispatcher
            ->expects($this->never())
            ->method('addListener')
        ;

        $service = new RegisterListenersService($dispatcher);

        $manager = $this->createMock(\Doctrine\ODM\MongoDB\DocumentManager::class);

        $pager = $this->createPagerMock();

        $service->register($manager, $pager, [
            'clear_object_manager' => false,
            'sleep' => 0,
            'debug_logging' => true,
        ]);
    }

    public function testShouldIgnoreDebugLoggingOptionForPHPCRManager()
    {
        if (!\interface_exists(\Doctrine\ODM\PHPCR\DocumentManagerInterface::class)) {
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

    /**
     * @return MockObject|PagerInterface
     */
    private function createPagerMock(): MockObject
    {
        return $this->createMock(PagerInterface::class);
    }

    /**
     * @return MockObject|ObjectPersisterInterface
     */
    private function createObjectPersisterMock(): MockObject
    {
        return $this->createMock(ObjectPersisterInterface::class);
    }

    /**
     * @return MockObject|ObjectManager
     */
    private function createObjectManagerMock(): MockObject
    {
        return $this->createMock(ObjectManager::class);
    }

    private function createDispatcher(): EventDispatcher
    {
        return new EventDispatcher();
    }

    /**
     * @return MockObject|EventDispatcherInterface
     */
    private function createDispatcherMock(): MockObject
    {
        return $this->createMock(EventDispatcherInterface::class);
    }
}
