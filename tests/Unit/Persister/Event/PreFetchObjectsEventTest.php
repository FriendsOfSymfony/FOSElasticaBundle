<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
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

final class PreFetchObjectsEventTest extends TestCase
{
    public function testShouldBeSubClassOfEventClass()
    {
        $rc = new \ReflectionClass(PreFetchObjectsEvent::class);

        $this->assertTrue($rc->isSubclassOf(Event::class));
    }

    public function testShouldImplementPersistEventInterface()
    {
        $rc = new \ReflectionClass(PreFetchObjectsEvent::class);

        $this->assertTrue($rc->implementsInterface(PersistEvent::class));
    }

    public function testShouldFinal()
    {
        $rc = new \ReflectionClass(PreFetchObjectsEvent::class);

        $this->assertTrue($rc->isFinal());
    }

    public function testCouldBeConstructedWithPagerAndObjectPersisterAndOptions()
    {
        new PreFetchObjectsEvent($this->createPagerMock(), $this->createObjectPersisterMock(), []);
    }

    public function testShouldAllowGetPagerSetInConstructor()
    {
        $expectedPager = $this->createPagerMock();

        $event = new PreFetchObjectsEvent($expectedPager, $this->createObjectPersisterMock(), []);

        $this->assertSame($expectedPager, $event->getPager());
    }

    public function testShouldAllowGetPreviouslySetPager()
    {
        $event = new PreFetchObjectsEvent($this->createPagerMock(), $this->createObjectPersisterMock(), []);

        $expectedPager = $this->createPagerMock();
        $event->setPager($expectedPager);

        $this->assertSame($expectedPager, $event->getPager());
    }

    public function testShouldAllowGetObjectPersisterSetInConstructor()
    {
        $expectedPersister = $this->createObjectPersisterMock();

        $event = new PreFetchObjectsEvent($this->createPagerMock(), $expectedPersister, []);

        $this->assertSame($expectedPersister, $event->getObjectPersister());
    }

    public function testShouldAllowGetPreviouslySetObjectsPersister()
    {
        $event = new PreFetchObjectsEvent($this->createPagerMock(), $this->createObjectPersisterMock(), []);

        $expectedPersister = $this->createObjectPersisterMock();
        $event->setObjectPersister($expectedPersister);

        $this->assertSame($expectedPersister, $event->getObjectPersister());
    }

    public function testShouldAllowGetOptionsSetInConstructor()
    {
        $expectedOptions = ['foo' => 'fooVal', 'bar' => 'barVal'];

        $event = new PreFetchObjectsEvent($this->createPagerMock(), $this->createObjectPersisterMock(), $expectedOptions);

        $this->assertSame($expectedOptions, $event->getOptions());
    }

    public function testShouldAllowGetPreviouslySetOptions()
    {
        $event = new PreFetchObjectsEvent($this->createPagerMock(), $this->createObjectPersisterMock(), ['foo' => 'fooVal', 'bar' => 'barVal']);

        $expectedOptions = ['foo' => 'fooNewVal', 'bar' => 'barnewVal'];
        $event->setOptions($expectedOptions);

        $this->assertSame($expectedOptions, $event->getOptions());
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
