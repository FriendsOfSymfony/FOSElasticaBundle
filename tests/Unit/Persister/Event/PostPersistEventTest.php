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
use FOS\ElasticaBundle\Persister\Event\PostPersistEvent;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Provider\PagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @internal
 */
final class PostPersistEventTest extends TestCase
{
    public function testShouldBeSubClassOfEventClass()
    {
        $rc = new \ReflectionClass(PostPersistEvent::class);

        $this->assertTrue($rc->isSubclassOf(Event::class));
    }

    public function testShouldImplementPersistEventInterface()
    {
        $rc = new \ReflectionClass(PostPersistEvent::class);

        $this->assertTrue($rc->implementsInterface(PersistEvent::class));
    }

    public function testShouldFinal()
    {
        $rc = new \ReflectionClass(PostPersistEvent::class);

        $this->assertTrue($rc->isFinal());
    }

    public function testCouldBeConstructedWithPagerAndObjectPersisterAndOptions()
    {
        new PostPersistEvent($this->createPagerMock(), $this->createObjectPersisterMock(), []);
    }

    public function testShouldAllowGetPagerSetInConstructor()
    {
        $expectedPager = $this->createPagerMock();

        $event = new PostPersistEvent($expectedPager, $this->createObjectPersisterMock(), []);

        $this->assertSame($expectedPager, $event->getPager());
    }

    public function testShouldAllowGetObjectPersisterSetInConstructor()
    {
        $expectedPersister = $this->createObjectPersisterMock();

        $event = new PostPersistEvent($this->createPagerMock(), $expectedPersister, []);

        $this->assertSame($expectedPersister, $event->getObjectPersister());
    }

    public function testShouldAllowGetOptionsSetInConstructor()
    {
        $expectedOptions = ['foo' => 'fooVal', 'bar' => 'barVal'];

        $event = new PostPersistEvent($this->createPagerMock(), $this->createObjectPersisterMock(), $expectedOptions);

        $this->assertSame($expectedOptions, $event->getOptions());
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
}
