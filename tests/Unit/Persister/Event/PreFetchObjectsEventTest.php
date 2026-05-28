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
use FOS\ElasticaBundle\Persister\Event\PreFetchObjectsEvent;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Provider\PagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @internal
 */
final class PreFetchObjectsEventTest extends TestCase
{
    public function testShouldBeSubClassOfEventClass(): void
    {
        $rc = new \ReflectionClass(PreFetchObjectsEvent::class);

        $this->assertTrue($rc->isSubclassOf(Event::class));
    }

    public function testShouldImplementPersistEventInterface(): void
    {
        $rc = new \ReflectionClass(PreFetchObjectsEvent::class);

        $this->assertTrue($rc->implementsInterface(PersistEvent::class));
    }

    public function testShouldFinal(): void
    {
        $rc = new \ReflectionClass(PreFetchObjectsEvent::class);

        $this->assertTrue($rc->isFinal());
    }

    public function testCouldBeConstructedWithPagerAndObjectPersisterAndOptions(): void
    {
        new PreFetchObjectsEvent($this->createPagerMock(), $this->createObjectPersisterMock(), []);
    }

    public function testShouldAllowGetPagerSetInConstructor(): void
    {
        $expectedPager = $this->createPagerMock();

        $event = new PreFetchObjectsEvent($expectedPager, $this->createObjectPersisterMock(), []);

        $this->assertSame($expectedPager, $event->getPager());
    }

    public function testShouldAllowGetPreviouslySetPager(): void
    {
        $event = new PreFetchObjectsEvent($this->createPagerMock(), $this->createObjectPersisterMock(), []);

        $expectedPager = $this->createPagerMock();
        $event->setPager($expectedPager);

        $this->assertSame($expectedPager, $event->getPager());
    }

    public function testShouldAllowGetObjectPersisterSetInConstructor(): void
    {
        $expectedPersister = $this->createObjectPersisterMock();

        $event = new PreFetchObjectsEvent($this->createPagerMock(), $expectedPersister, []);

        $this->assertSame($expectedPersister, $event->getObjectPersister());
    }

    public function testShouldAllowGetPreviouslySetObjectsPersister(): void
    {
        $event = new PreFetchObjectsEvent($this->createPagerMock(), $this->createObjectPersisterMock(), []);

        $expectedPersister = $this->createObjectPersisterMock();
        $event->setObjectPersister($expectedPersister);

        $this->assertSame($expectedPersister, $event->getObjectPersister());
    }

    public function testShouldAllowGetOptionsSetInConstructor(): void
    {
        $expectedOptions = ['foo' => 'fooVal', 'bar' => 'barVal'];

        $event = new PreFetchObjectsEvent($this->createPagerMock(), $this->createObjectPersisterMock(), $expectedOptions);

        $this->assertSame($expectedOptions, $event->getOptions());
    }

    public function testShouldAllowGetPreviouslySetOptions(): void
    {
        $event = new PreFetchObjectsEvent($this->createPagerMock(), $this->createObjectPersisterMock(), ['foo' => 'fooVal', 'bar' => 'barVal']);

        $expectedOptions = ['foo' => 'fooNewVal', 'bar' => 'barnewVal'];
        $event->setOptions($expectedOptions);

        $this->assertSame($expectedOptions, $event->getOptions());
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
