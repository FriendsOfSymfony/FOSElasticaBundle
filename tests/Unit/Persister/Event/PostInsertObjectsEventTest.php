<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Persister\Event;

use FOS\ElasticaBundle\Persister\Event\PersistEvent;
use FOS\ElasticaBundle\Persister\Event\PostInsertObjectsEvent;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Provider\PagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @internal
 */
final class PostInsertObjectsEventTest extends TestCase
{
    public function testShouldBeSubClassOfEventClass(): void
    {
        $rc = new \ReflectionClass(PostInsertObjectsEvent::class);

        $this->assertTrue($rc->isSubclassOf(Event::class));
    }

    public function testShouldImplementPersistEventInterface(): void
    {
        $rc = new \ReflectionClass(PostInsertObjectsEvent::class);

        $this->assertTrue($rc->implementsInterface(PersistEvent::class));
    }

    public function testShouldFinal(): void
    {
        $rc = new \ReflectionClass(PostInsertObjectsEvent::class);

        $this->assertTrue($rc->isFinal());
    }

    public function testCouldBeConstructedWithPagerAndObjectPersisterAndObjectsAndOptions(): void
    {
        new PostInsertObjectsEvent(
            $this->createPagerMock(),
            $this->createObjectPersisterMock(),
            $objects = [],
            $options = []
        );
    }

    public function testShouldAllowGetPagerSetInConstructor(): void
    {
        $expectedPager = $this->createPagerMock();

        $event = new PostInsertObjectsEvent($expectedPager, $this->createObjectPersisterMock(), [], []);

        $this->assertSame($expectedPager, $event->getPager());
    }

    public function testShouldAllowGetObjectPersisterSetInConstructor(): void
    {
        $expectedPersister = $this->createObjectPersisterMock();

        $event = new PostInsertObjectsEvent($this->createPagerMock(), $expectedPersister, [], []);

        $this->assertSame($expectedPersister, $event->getObjectPersister());
    }

    public function testShouldAllowGetOptionsSetInConstructor(): void
    {
        $expectedOptions = ['foo' => 'fooVal', 'bar' => 'barVal'];

        $event = new PostInsertObjectsEvent($this->createPagerMock(), $this->createObjectPersisterMock(), [], $expectedOptions);

        $this->assertSame($expectedOptions, $event->getOptions());
    }

    public function testShouldAllowGetObjectsSetInConstructor(): void
    {
        $expectedObjects = [new \stdClass(), new \stdClass()];

        $event = new PostInsertObjectsEvent($this->createPagerMock(), $this->createObjectPersisterMock(), $expectedObjects, []);

        $this->assertSame($expectedObjects, $event->getObjects());
    }

    /**
     * @return ObjectPersisterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function createObjectPersisterMock(): \PHPUnit\Framework\MockObject\MockObject
    {
        return $this->createMock(ObjectPersisterInterface::class);
    }

    /**
     * @return PagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function createPagerMock(): \PHPUnit\Framework\MockObject\MockObject
    {
        return $this->createMock(PagerInterface::class);
    }
}
