<?php
namespace FOS\ElasticaBundle\Tests\Persister\Event;

use FOS\ElasticaBundle\Persister\Event\OnExceptionEvent;
use FOS\ElasticaBundle\Persister\Event\PersistEvent;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Provider\PagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\Event;

final class OnExceptionEventTest extends TestCase
{
    public function testShouldBeSubClassOfEventClass()
    {
        $rc = new \ReflectionClass(OnExceptionEvent::class);

        $this->assertTrue($rc->isSubclassOf(Event::class));
    }

    public function testShouldImplementPersistEventInterface()
    {
        $rc = new \ReflectionClass(OnExceptionEvent::class);

        $this->assertTrue($rc->implementsInterface(PersistEvent::class));
    }

    public function testShouldFinal()
    {
        $rc = new \ReflectionClass(OnExceptionEvent::class);

        $this->assertTrue($rc->isFinal());
    }

    public function testCouldBeConstructedWithExpectedArguments()
    {
        new OnExceptionEvent(
            $this->createPagerMock(),
            $this->createObjectPersisterMock(), new \Exception(),
            $objects = [],
            $options = []
        );
    }

    public function testShouldAllowGetPagerSetInConstructor()
    {
        $expectedPager = $this->createPagerMock();

        $event = new OnExceptionEvent($expectedPager, $this->createObjectPersisterMock(), new \Exception(), [], []);

        $this->assertSame($expectedPager, $event->getPager());
    }

    public function testShouldAllowGetObjectPersisterSetInConstructor()
    {
        $expectedPersister = $this->createObjectPersisterMock();

        $event = new OnExceptionEvent($this->createPagerMock(), $expectedPersister, new \Exception(), [], []);

        $this->assertSame($expectedPersister, $event->getObjectPersister());
    }

    public function testShouldAllowGetOptionsSetInConstructor()
    {
        $expectedOptions = ['foo' => 'fooVal', 'bar' => 'barVal'];

        $event = new OnExceptionEvent($this->createPagerMock(), $this->createObjectPersisterMock(), new \Exception(), [], $expectedOptions);

        $this->assertSame($expectedOptions, $event->getOptions());
    }

    public function testShouldAllowGetObjectsSetInConstructor()
    {
        $expectedObjects = [new \stdClass(), new \stdClass()];

        $event = new OnExceptionEvent($this->createPagerMock(), $this->createObjectPersisterMock(), new \Exception(), $expectedObjects, []);

        $this->assertSame($expectedObjects, $event->getObjects());
    }

    public function testShouldAllowGetExceptionSetInConstructor()
    {
        $expectedException = new \Exception();

        $event = new OnExceptionEvent($this->createPagerMock(), $this->createObjectPersisterMock(), $expectedException, [], []);

        $this->assertSame($expectedException, $event->getException());
    }

    public function testShouldAllowGetPreviouslySetException()
    {
        $event = new OnExceptionEvent($this->createPagerMock(), $this->createObjectPersisterMock(), new \Exception(), [], []);

        $expectedException = new \Exception();
        $event->setException($expectedException);

        $this->assertSame($expectedException, $event->getException());
    }

    public function testShouldNotIgnoreExceptionByDefault()
    {
        $event = new OnExceptionEvent($this->createPagerMock(), $this->createObjectPersisterMock(), new \Exception(), [], []);

        $this->assertFalse($event->isIgnored());
    }

    public function testShouldAllowIgnoreException()
    {
        $event = new OnExceptionEvent($this->createPagerMock(), $this->createObjectPersisterMock(), new \Exception(), [], []);

        $event->setIgnore(true);

        $this->assertTrue($event->isIgnored());
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
