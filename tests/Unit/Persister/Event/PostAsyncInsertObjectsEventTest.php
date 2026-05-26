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
use FOS\ElasticaBundle\Persister\Event\PostAsyncInsertObjectsEvent;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Provider\PagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @internal
 *
 * @deprecated since 6.3 will be removed in 7.0
 */
final class PostAsyncInsertObjectsEventTest extends TestCase
{
    public function testShouldBeSubClassOfEventClass(): void
    {
        $rc = new \ReflectionClass(PostAsyncInsertObjectsEvent::class);

        $this->assertTrue($rc->isSubclassOf(Event::class));
    }

    public function testShouldImplementPersistEventInterface(): void
    {
        $rc = new \ReflectionClass(PostAsyncInsertObjectsEvent::class);

        $this->assertTrue($rc->implementsInterface(PersistEvent::class));
    }

    public function testShouldFinal(): void
    {
        $rc = new \ReflectionClass(PostAsyncInsertObjectsEvent::class);

        $this->assertTrue($rc->isFinal());
    }

    public function testCouldBeConstructedWithPagerAndObjectPersisterAndObjectsCountAndOptions(): void
    {
        new PostAsyncInsertObjectsEvent(
            $this->createPagerMock(),
            $this->createObjectPersisterMock(),
            123,
            $errorMessage = '',
            $options = []
        );
    }

    public function testShouldAllowGetPagerSetInConstructor(): void
    {
        $expectedPager = $this->createPagerMock();

        $event = new PostAsyncInsertObjectsEvent($expectedPager, $this->createObjectPersisterMock(), 123, '', []);

        $this->assertSame($expectedPager, $event->getPager());
    }

    public function testShouldAllowGetObjectPersisterSetInConstructor(): void
    {
        $expectedPersister = $this->createObjectPersisterMock();

        $event = new PostAsyncInsertObjectsEvent($this->createPagerMock(), $expectedPersister, 123, '', []);

        $this->assertSame($expectedPersister, $event->getObjectPersister());
    }

    public function testShouldAllowGetOptionsSetInConstructor(): void
    {
        $expectedOptions = ['foo' => 'fooVal', 'bar' => 'barVal'];

        $event = new PostAsyncInsertObjectsEvent($this->createPagerMock(), $this->createObjectPersisterMock(), 123, '', $expectedOptions);

        $this->assertSame($expectedOptions, $event->getOptions());
    }

    public function testShouldAllowGetObjectsSetInConstructor(): void
    {
        $expectedObjectsCount = 321;

        $event = new PostAsyncInsertObjectsEvent($this->createPagerMock(), $this->createObjectPersisterMock(), $expectedObjectsCount, '', []);

        $this->assertSame($expectedObjectsCount, $event->getObjectsCount());
    }

    public function testShouldAllowGetErrorMessageSetInConstructor(): void
    {
        $expectedErrorMessage = 'theErrorMessage';

        $event = new PostAsyncInsertObjectsEvent($this->createPagerMock(), $this->createObjectPersisterMock(), 0, 'theErrorMessage', []);

        $this->assertSame($expectedErrorMessage, $event->getErrorMessage());
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
