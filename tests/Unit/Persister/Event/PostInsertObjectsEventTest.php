<?php
namespace FOS\ElasticaBundle\Tests\Unit\Persister\Event;

use FOS\ElasticaBundle\Persister\Event\PersistEvent;
use FOS\ElasticaBundle\Persister\Event\PostInsertObjectsEvent;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Provider\PagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\Event;

final class PostInsertObjectsEventTest extends TestCase
{
    public function testShouldBeSubClassOfEventClass()
    {
        $rc = new \ReflectionClass(PostInsertObjectsEvent::class);

        $this->assertTrue($rc->isSubclassOf(Event::class));
    }

    public function testShouldImplementPersistEventInterface()
    {
        $rc = new \ReflectionClass(PostInsertObjectsEvent::class);

        $this->assertTrue($rc->implementsInterface(PersistEvent::class));
    }

    public function testShouldFinal()
    {
        $rc = new \ReflectionClass(PostInsertObjectsEvent::class);

        $this->assertTrue($rc->isFinal());
    }

    public function testCouldBeConstructedWithPagerAndObjectPersisterAndObjectsAndOptions()
    {
        new PostInsertObjectsEvent(
            $this->createPagerMock(),
            $this->createObjectPersisterMock(),
            $objects = [],
            $options = []
        );
    }

    public function testShouldAllowGetPagerSetInConstructor()
    {
        $expectedPager = $this->createPagerMock();

        $event = new PostInsertObjectsEvent($expectedPager, $this->createObjectPersisterMock(), [], []);

        $this->assertSame($expectedPager, $event->getPager());
    }

    public function testShouldAllowGetObjectPersisterSetInConstructor()
    {
        $expectedPersister = $this->createObjectPersisterMock();

        $event = new PostInsertObjectsEvent($this->createPagerMock(), $expectedPersister, [], []);

        $this->assertSame($expectedPersister, $event->getObjectPersister());
    }

    public function testShouldAllowGetOptionsSetInConstructor()
    {
        $expectedOptions = ['foo' => 'fooVal', 'bar' => 'barVal'];

        $event = new PostInsertObjectsEvent($this->createPagerMock(), $this->createObjectPersisterMock(), [], $expectedOptions);

        $this->assertSame($expectedOptions, $event->getOptions());
    }

    public function testShouldAllowGetObjectsSetInConstructor()
    {
        $expectedObjects = [new \stdClass(), new \stdClass()];

        $event = new PostInsertObjectsEvent($this->createPagerMock(), $this->createObjectPersisterMock(), $expectedObjects, []);

        $this->assertSame($expectedObjects, $event->getObjects());
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
