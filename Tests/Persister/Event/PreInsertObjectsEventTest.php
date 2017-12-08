<?php
namespace FOS\ElasticaBundle\Tests\Persister\Event;

use FOS\ElasticaBundle\Persister\Event\PersistEvent;
use FOS\ElasticaBundle\Persister\Event\PreInsertObjectsEvent;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Provider\PagerInterface;
use Symfony\Component\EventDispatcher\Event;

final class PreInsertObjectsEventTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldBeSubClassOfEventClass()
    {
        $rc = new \ReflectionClass(PreInsertObjectsEvent::class);

        $this->assertTrue($rc->isSubclassOf(Event::class));
    }

    public function testShouldImplementPersistEventInterface()
    {
        $rc = new \ReflectionClass(PreInsertObjectsEvent::class);

        $this->assertTrue($rc->implementsInterface(PersistEvent::class));
    }

    public function testShouldFinal()
    {
        $rc = new \ReflectionClass(PreInsertObjectsEvent::class);

        $this->assertTrue($rc->isFinal());
    }

    public function testCouldBeConstructedWithPagerAndObjectPersisterAndObjectsAndOptions()
    {
        new PreInsertObjectsEvent(
            $this->createPagerMock(),
            $this->createObjectPersisterMock(),
            $objects = [],
            $options = []
        );
    }

    public function testShouldAllowGetPagerSetInConstructor()
    {
        $expectedPager = $this->createPagerMock();

        $event = new PreInsertObjectsEvent($expectedPager, $this->createObjectPersisterMock(), [], []);

        $this->assertSame($expectedPager, $event->getPager());
    }

    public function testShouldAllowGetPreviouslySetPager()
    {
        $event = new PreInsertObjectsEvent($this->createPagerMock(), $this->createObjectPersisterMock(), [], []);

        $expectedPager = $this->createPagerMock();
        $event->setPager($expectedPager);

        $this->assertSame($expectedPager, $event->getPager());
    }

    public function testShouldAllowGetObjectPersisterSetInConstructor()
    {
        $expectedPersister = $this->createObjectPersisterMock();

        $event = new PreInsertObjectsEvent($this->createPagerMock(), $expectedPersister, [], []);

        $this->assertSame($expectedPersister, $event->getObjectPersister());
    }

    public function testShouldAllowGetPreviouslySetObjectsPersister()
    {
        $event = new PreInsertObjectsEvent($this->createPagerMock(), $this->createObjectPersisterMock(), [], []);

        $expectedPersister = $this->createObjectPersisterMock();
        $event->setObjectPersister($expectedPersister);

        $this->assertSame($expectedPersister, $event->getObjectPersister());
    }

    public function testShouldAllowGetOptionsSetInConstructor()
    {
        $expectedOptions = ['foo' => 'fooVal', 'bar' => 'barVal'];

        $event = new PreInsertObjectsEvent($this->createPagerMock(), $this->createObjectPersisterMock(), [], $expectedOptions);

        $this->assertSame($expectedOptions, $event->getOptions());
    }

    public function testShouldAllowGetPreviouslySetOptions()
    {
        $event = new PreInsertObjectsEvent($this->createPagerMock(), $this->createObjectPersisterMock(), [], ['foo' => 'fooVal', 'bar' => 'barVal']);

        $expectedOptions = ['foo' => 'fooNewVal', 'bar' => 'barnewVal'];
        $event->setOptions($expectedOptions);

        $this->assertSame($expectedOptions, $event->getOptions());
    }

    public function testShouldAllowGetObjectsSetInConstructor()
    {
        $expectedObjects = [new \stdClass(), new \stdClass()];

        $event = new PreInsertObjectsEvent($this->createPagerMock(), $this->createObjectPersisterMock(), $expectedObjects, []);

        $this->assertSame($expectedObjects, $event->getObjects());
    }

    public function testShouldAllowGetPreviouslySetObjects()
    {
        $event = new PreInsertObjectsEvent($this->createPagerMock(), $this->createObjectPersisterMock(), [new \stdClass(), new \stdClass()], []);

        $expectedObjects = [new \stdClass(), new \stdClass()];
        $event->setObjects($expectedObjects);

        $this->assertSame($expectedObjects, $event->getObjects());
    }

    /**
     * @return ObjectPersisterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createObjectPersisterMock()
    {
        return $this->getMock(ObjectPersisterInterface::class, [], [], '', false);
    }

    /**
     * @return PagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createPagerMock()
    {
        return $this->getMock(PagerInterface::class, [], [], '', false);
    }
}
