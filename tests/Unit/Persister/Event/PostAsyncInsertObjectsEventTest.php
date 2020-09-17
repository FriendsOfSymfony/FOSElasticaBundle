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

final class PostAsyncInsertObjectsEventTest extends TestCase
{
    public function testShouldBeSubClassOfEventClass()
    {
        $rc = new \ReflectionClass(PostAsyncInsertObjectsEvent::class);

        $this->assertTrue($rc->isSubclassOf(Event::class));
    }

    public function testShouldImplementPersistEventInterface()
    {
        $rc = new \ReflectionClass(PostAsyncInsertObjectsEvent::class);

        $this->assertTrue($rc->implementsInterface(PersistEvent::class));
    }

    public function testShouldFinal()
    {
        $rc = new \ReflectionClass(PostAsyncInsertObjectsEvent::class);

        $this->assertTrue($rc->isFinal());
    }

    public function testCouldBeConstructedWithPagerAndObjectPersisterAndObjectsCountAndOptions()
    {
        new PostAsyncInsertObjectsEvent(
            $this->createPagerMock(),
            $this->createObjectPersisterMock(),
            123,
            $errorMessage = '',
            $options = []
        );
    }

    public function testShouldAllowGetPagerSetInConstructor()
    {
        $expectedPager = $this->createPagerMock();

        $event = new PostAsyncInsertObjectsEvent($expectedPager, $this->createObjectPersisterMock(), 123, '', []);

        $this->assertSame($expectedPager, $event->getPager());
    }

    public function testShouldAllowGetObjectPersisterSetInConstructor()
    {
        $expectedPersister = $this->createObjectPersisterMock();

        $event = new PostAsyncInsertObjectsEvent($this->createPagerMock(), $expectedPersister, 123, '', []);

        $this->assertSame($expectedPersister, $event->getObjectPersister());
    }

    public function testShouldAllowGetOptionsSetInConstructor()
    {
        $expectedOptions = ['foo' => 'fooVal', 'bar' => 'barVal'];

        $event = new PostAsyncInsertObjectsEvent($this->createPagerMock(), $this->createObjectPersisterMock(), 123, '', $expectedOptions);

        $this->assertSame($expectedOptions, $event->getOptions());
    }

    public function testShouldAllowGetObjectsSetInConstructor()
    {
        $expectedObjectsCount = 321;

        $event = new PostAsyncInsertObjectsEvent($this->createPagerMock(), $this->createObjectPersisterMock(), $expectedObjectsCount, '', []);

        $this->assertSame($expectedObjectsCount, $event->getObjectsCount());
    }

    public function testShouldAllowGetErrorMessageSetInConstructor()
    {
        $expectedErrorMessage = 'theErrorMessage';

        $event = new PostAsyncInsertObjectsEvent($this->createPagerMock(), $this->createObjectPersisterMock(), 0, 'theErrorMessage', []);

        $this->assertSame($expectedErrorMessage, $event->getErrorMessage());
    }

    /**
     * @return ObjectPersisterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createObjectPersisterMock()
    {
        return $this->createMock(ObjectPersisterInterface::class);
    }

    /**
     * @return PagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createPagerMock()
    {
        return $this->createMock(PagerInterface::class);
    }
}
