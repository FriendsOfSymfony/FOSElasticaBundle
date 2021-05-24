<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Persister\Listener;

use FOS\ElasticaBundle\Persister\Event\PreInsertObjectsEvent;
use FOS\ElasticaBundle\Persister\Listener\FilterObjectsListener;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Provider\IndexableInterface;
use FOS\ElasticaBundle\Provider\PagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
class FilterObjectsListenerTest extends TestCase
{
    public function testShouldImplementEventSubscriberInterface()
    {
        $rc = new \ReflectionClass(FilterObjectsListener::class);

        $this->assertTrue($rc->implementsInterface(EventSubscriberInterface::class));
    }

    public function testShouldSubscribeOnPreInsertObjectsEvent()
    {
        $this->assertSame([PreInsertObjectsEvent::class => 'filterObjects'], FilterObjectsListener::getSubscribedEvents());
    }

    public function testCouldBeConstructedWithIndexableAsFirstArgument()
    {
        new FilterObjectsListener($this->createIndexableMock());
    }

    public function testShouldFilterOutEverything()
    {
        $objects = [new \stdClass(), new \stdClass(), new \stdClass()];

        $indexableMock = $this->createIndexableMock();
        $indexableMock
            ->expects($this->exactly(3))
            ->method('isObjectIndexable')
            ->withConsecutive(
                ['theIndex', $this->identicalTo($objects[0])],
                ['theIndex', $this->identicalTo($objects[1])],
                ['theIndex', $this->identicalTo($objects[2])]
            )
            ->willReturn(false)
        ;

        $listener = new FilterObjectsListener($indexableMock);

        $event = new PreInsertObjectsEvent(
            $this->createPagerMock(),
            $this->createObjectPersisterMock(),
            $objects,
            ['indexName' => 'theIndex']
        );

        $listener->filterObjects($event);

        $this->assertEmpty($event->getObjects());
    }

    public function testShouldFilterSecondObject()
    {
        $objects = [new \stdClass(), new \stdClass(), new \stdClass()];

        $indexableMock = $this->createIndexableMock();
        $indexableMock
            ->expects($this->exactly(3))
            ->method('isObjectIndexable')
            ->withConsecutive(
                ['theIndex', $this->identicalTo($objects[0])],
                ['theIndex', $this->identicalTo($objects[1])],
                ['theIndex', $this->identicalTo($objects[2])]
            )
            ->willReturnOnConsecutiveCalls(true, false, true)
        ;

        $listener = new FilterObjectsListener($indexableMock);

        $event = new PreInsertObjectsEvent(
            $this->createPagerMock(),
            $this->createObjectPersisterMock(),
            $objects,
            ['indexName' => 'theIndex']
        );

        $listener->filterObjects($event);

        $this->assertSame([$objects[0], $objects[2]], $event->getObjects());
    }

    public function testShouldSkipIndexableCheckIfOptionTrue()
    {
        $objects = [new \stdClass(), new \stdClass(), new \stdClass()];

        $indexableMock = $this->createIndexableMock();
        $indexableMock
            ->expects($this->never())
            ->method('isObjectIndexable')
        ;

        $listener = new FilterObjectsListener($indexableMock);

        $event = new PreInsertObjectsEvent(
            $this->createPagerMock(),
            $this->createObjectPersisterMock(),
            $objects,
            ['indexName' => 'theIndex', 'skip_indexable_check' => true]
        );

        $listener->filterObjects($event);

        $this->assertSame($objects, $event->getObjects());
    }

    /**
     * @return ObjectPersisterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function createObjectPersisterMock()
    {
        return $this->createMock(ObjectPersisterInterface::class);
    }

    /**
     * @return PagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function createPagerMock()
    {
        return $this->createMock(PagerInterface::class);
    }

    /**
     * @return IndexableInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function createIndexableMock()
    {
        return $this->createMock(IndexableInterface::class);
    }
}
