<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Persister;

use FOS\ElasticaBundle\Persister\Event\OnExceptionEvent;
use FOS\ElasticaBundle\Persister\Event\PostInsertObjectsEvent;
use FOS\ElasticaBundle\Persister\Event\PostPersistEvent;
use FOS\ElasticaBundle\Persister\Event\PreFetchObjectsEvent;
use FOS\ElasticaBundle\Persister\Event\PreInsertObjectsEvent;
use FOS\ElasticaBundle\Persister\Event\PrePersistEvent;
use FOS\ElasticaBundle\Persister\InPlacePagerPersister;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Persister\PagerPersisterInterface;
use FOS\ElasticaBundle\Persister\PersisterRegistry;
use FOS\ElasticaBundle\Provider\PagerfantaPager;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class InPlacePagerPersisterTest extends TestCase
{
    public function testShouldImplementPagerPersisterInterface()
    {
        $rc = new \ReflectionClass(InPlacePagerPersister::class);

        $this->assertTrue($rc->implementsInterface(PagerPersisterInterface::class));
    }

    public function testCouldBeConstructedWithPersisterRegistryAndDispatcherAsArguments()
    {
        new InPlacePagerPersister($this->createPersisterRegistryMock(), new EventDispatcher());
    }

    public function testShouldDispatchPrePersistEventWithExpectedArguments()
    {
        $objectPersisterMock = $this->createObjectPersisterMock();

        $options = ['indexName' => 'theIndex'];

        $registryMock = $this->createPersisterRegistryStub($objectPersisterMock);
        $dispatcher = new EventDispatcher();

        $persister = new InPlacePagerPersister($registryMock, $dispatcher);

        $pager = $this->createPager([new \stdClass(), new \stdClass()]);

        $called = false;
        $dispatcher->addListener(PrePersistEvent::class, function ($event) use (&$called, $pager, $objectPersisterMock, $options) {
            $called = true;

            $this->assertInstanceOf(PrePersistEvent::class, $event);
            $this->assertSame($pager, $event->getPager());
            $this->assertSame($objectPersisterMock, $event->getObjectPersister());
            $this->assertSame(
                ['max_per_page' => 100, 'first_page' => 1, 'last_page' => 1] + $options,
                $event->getOptions()
            );
        });

        $persister->insert($pager, $options);

        $this->assertTrue($called);
    }

    public function testShouldDispatchPreFetchObjectsEventWithExpectedArguments()
    {
        $objectPersisterMock = $this->createObjectPersisterMock();

        $options = ['indexName' => 'theIndex'];

        $registryMock = $this->createPersisterRegistryStub($objectPersisterMock);
        $dispatcher = new EventDispatcher();

        $persister = new InPlacePagerPersister($registryMock, $dispatcher);

        $objects = [new \stdClass(), new \stdClass()];

        $pager = $this->createPager($objects);

        $called = false;
        $dispatcher->addListener(PreFetchObjectsEvent::class, function ($event) use (&$called, $pager, $objectPersisterMock, $options) {
            $called = true;

            $this->assertInstanceOf(PreFetchObjectsEvent::class, $event);
            $this->assertSame($pager, $event->getPager());
            $this->assertSame($objectPersisterMock, $event->getObjectPersister());
            $this->assertSame(
                ['max_per_page' => 100, 'first_page' => 1, 'last_page' => 1] + $options,
                $event->getOptions()
            );
        });

        $persister->insert($pager, $options);

        $this->assertTrue($called);
    }

    public function testShouldDispatchPreInsertObjectsEventWithExpectedArguments()
    {
        $objectPersisterMock = $this->createObjectPersisterMock();

        $options = ['indexName' => 'theIndex'];

        $registryMock = $this->createPersisterRegistryStub($objectPersisterMock);
        $dispatcher = new EventDispatcher();

        $persister = new InPlacePagerPersister($registryMock, $dispatcher);

        $objects = [new \stdClass(), new \stdClass()];

        $pager = $this->createPager($objects);

        $called = false;
        $dispatcher->addListener(PreInsertObjectsEvent::class, function ($event) use (&$called, $pager, $objectPersisterMock, $objects, $options) {
            $called = true;

            $this->assertInstanceOf(PreInsertObjectsEvent::class, $event);
            $this->assertSame($pager, $event->getPager());
            $this->assertSame($objectPersisterMock, $event->getObjectPersister());
            $this->assertSame(
                ['max_per_page' => 100, 'first_page' => 1, 'last_page' => 1] + $options,
                $event->getOptions()
            );
            $this->assertSame($objects, $event->getObjects());
        });

        $persister->insert($pager, $options);

        $this->assertTrue($called);
    }

    public function testShouldDispatchPostInsertObjectsEventWithExpectedArguments()
    {
        $objectPersisterMock = $this->createObjectPersisterMock();

        $options = ['indexName' => 'theIndex'];

        $registryMock = $this->createPersisterRegistryStub($objectPersisterMock);
        $dispatcher = new EventDispatcher();

        $persister = new InPlacePagerPersister($registryMock, $dispatcher);

        $objects = [new \stdClass(), new \stdClass()];

        $pager = $this->createPager($objects);

        $called = false;
        $dispatcher->addListener(PostInsertObjectsEvent::class, function ($event) use (&$called, $pager, $objectPersisterMock, $objects, $options) {
            $called = true;

            $this->assertInstanceOf(PostInsertObjectsEvent::class, $event);
            $this->assertSame($pager, $event->getPager());
            $this->assertSame($objectPersisterMock, $event->getObjectPersister());
            $this->assertSame(
                ['max_per_page' => 100, 'first_page' => 1, 'last_page' => 1] + $options,
                $event->getOptions()
            );
            $this->assertSame($objects, $event->getObjects());
        });

        $persister->insert($pager, $options);

        $this->assertTrue($called);
    }

    public function testShouldDispatchPostPersistEventWithExpectedArguments()
    {
        $objectPersisterMock = $this->createObjectPersisterMock();

        $options = ['indexName' => 'theIndex'];

        $registryMock = $this->createPersisterRegistryStub($objectPersisterMock);
        $dispatcher = new EventDispatcher();

        $persister = new InPlacePagerPersister($registryMock, $dispatcher);

        $objects = [new \stdClass(), new \stdClass()];

        $pager = $this->createPager($objects);

        $called = false;
        $dispatcher->addListener(PostPersistEvent::class, function ($event) use (&$called, $pager, $objectPersisterMock, $objects, $options) {
            $called = true;

            $this->assertInstanceOf(PostPersistEvent::class, $event);
            $this->assertSame($pager, $event->getPager());
            $this->assertSame($objectPersisterMock, $event->getObjectPersister());
            $this->assertSame(
                ['max_per_page' => 100, 'first_page' => 1, 'last_page' => 1] + $options,
                $event->getOptions()
            );
        });

        $persister->insert($pager, $options);

        $this->assertTrue($called);
    }

    public function testShouldCallObjectPersisterInsertManyMethodForEachPage()
    {
        $options = ['indexName' => 'theIndex', 'max_per_page' => 2];

        $firstPage = [new \stdClass(), new \stdClass()];
        $secondPage = [new \stdClass(), new \stdClass()];
        $thirdPage = [new \stdClass(), new \stdClass()];

        $objects = [$firstPage[0], $firstPage[1], $secondPage[0], $secondPage[1], $thirdPage[0], $thirdPage[1]];

        $objectPersisterMock = $this->createObjectPersisterMock();
        $objectPersisterMock
            ->expects($this->exactly(3))
            ->method('insertMany')
            ->withConsecutive([$this->identicalTo($firstPage)], [$this->identicalTo($secondPage)], [$this->identicalTo($thirdPage)])
        ;

        $registryMock = $this->createPersisterRegistryStub($objectPersisterMock);

        $persister = new InPlacePagerPersister($registryMock, new EventDispatcher());

        $pager = $this->createPager($objects);

        $persister->insert($pager, $options);
    }

    public function testShouldCallObjectPersisterInsertManyMethodOnlyForSecondPage()
    {
        $options = [
            'indexName' => 'theIndex',
            'max_per_page' => 2,
            'first_page' => 2,
            'last_page' => 2,
        ];

        $firstPage = [new \stdClass(), new \stdClass()];
        $secondPage = [new \stdClass(), new \stdClass()];
        $thirdPage = [new \stdClass(), new \stdClass()];

        $objects = [$firstPage[0], $firstPage[1], $secondPage[0], $secondPage[1], $thirdPage[0], $thirdPage[1]];

        $objectPersisterMock = $this->createObjectPersisterMock();
        $objectPersisterMock
            ->expects($this->once())
            ->method('insertMany')
            ->with($this->identicalTo($secondPage))
        ;

        $registryMock = $this->createPersisterRegistryStub($objectPersisterMock);

        $persister = new InPlacePagerPersister($registryMock, new EventDispatcher());

        $pager = $this->createPager($objects);

        $persister->insert($pager, $options);
    }

    public function testShouldIterateToRealLastPageEvenIfLastPageOptionIsBigger()
    {
        $options = [
            'indexName' => 'theIndex',
            'max_per_page' => 2,
            'last_page' => 100,
        ];

        $firstPage = [new \stdClass(), new \stdClass()];
        $secondPage = [new \stdClass(), new \stdClass()];
        $thirdPage = [new \stdClass(), new \stdClass()];

        $objects = [$firstPage[0], $firstPage[1], $secondPage[0], $secondPage[1], $thirdPage[0], $thirdPage[1]];

        $objectPersisterMock = $this->createObjectPersisterMock();
        $objectPersisterMock
            ->expects($this->exactly(3))
            ->method('insertMany')
        ;

        $registryMock = $this->createPersisterRegistryStub($objectPersisterMock);

        $persister = new InPlacePagerPersister($registryMock, new EventDispatcher());

        $pager = $this->createPager($objects);

        $persister->insert($pager, $options);
    }

    public function testShouldDispatchOnExceptionEventWithExpectedArgumentsAndReThrowIt()
    {
        $exception = new \LogicException();

        $objectPersisterMock = $this->createObjectPersisterMock();
        $objectPersisterMock
            ->expects($this->once())
            ->method('insertMany')
            ->willThrowException($exception)
        ;

        $options = ['indexName' => 'theIndex'];

        $registryMock = $this->createPersisterRegistryStub($objectPersisterMock);
        $dispatcher = new EventDispatcher();

        $persister = new InPlacePagerPersister($registryMock, $dispatcher);

        $objects = [new \stdClass(), new \stdClass()];

        $pager = $this->createPager($objects);

        $called = false;
        $dispatcher->addListener(OnExceptionEvent::class, function ($event) use (&$called, $pager, $objectPersisterMock, $exception, $options) {
            $called = true;

            $this->assertInstanceOf(OnExceptionEvent::class, $event);
            $this->assertSame($pager, $event->getPager());
            $this->assertSame($objectPersisterMock, $event->getObjectPersister());
            $this->assertSame(
                ['max_per_page' => 100, 'first_page' => 1, 'last_page' => 1] + $options,
                $event->getOptions()
            );
            $this->assertSame($exception, $event->getException());
        });

        try {
            $persister->insert($pager, $options);
        } catch (\Exception $e) {
            $this->assertTrue($called);
            $this->assertSame($exception, $e);

            return;
        }

        $this->fail('The exception is expected to be thrown');
    }

    public function testShouldDispatchOnExceptionEventButNotReThrowIt()
    {
        $exception = new \LogicException();

        $objectPersisterMock = $this->createObjectPersisterMock();
        $objectPersisterMock
            ->expects($this->once())
            ->method('insertMany')
            ->willThrowException($exception)
        ;

        $options = ['indexName' => 'theIndex'];

        $registryMock = $this->createPersisterRegistryStub($objectPersisterMock);
        $dispatcher = new EventDispatcher();

        $persister = new InPlacePagerPersister($registryMock, $dispatcher);

        $objects = [new \stdClass(), new \stdClass()];

        $pager = $this->createPager($objects);

        $called = false;
        $dispatcher->addListener(OnExceptionEvent::class, function (OnExceptionEvent $event) use (&$called) {
            $called = true;

            $event->setIgnored(true);
        });

        $persister->insert($pager, $options);

        $this->assertTrue($called);
    }

    private function createPager(array $objects)
    {
        return new PagerfantaPager(new Pagerfanta(new ArrayAdapter($objects)));
    }

    /**
     * @return ObjectPersisterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createObjectPersisterMock()
    {
        return $this->createMock(ObjectPersisterInterface::class);
    }

    /**
     * @return PersisterRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createPersisterRegistryMock()
    {
        return $this->createMock(PersisterRegistry::class);
    }

    /**
     * @return PersisterRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createPersisterRegistryStub($objectPersister = null)
    {
        $registryMock = $this->createPersisterRegistryMock();
        $registryMock
            ->expects($this->once())
            ->method('getPersister')
            ->with('theIndex')
            ->willReturn($objectPersister)
        ;

        return $registryMock;
    }
}
