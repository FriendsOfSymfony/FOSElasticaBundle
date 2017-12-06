<?php
namespace FOS\ElasticaBundle\Tests\Persister\Event;

use FOS\ElasticaBundle\Persister\Event\PrePersistEvent;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Provider\PagerInterface;
use Symfony\Component\EventDispatcher\Event;

final class PrePersistEventTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldBeSubClassOfEventClass()
    {
        $rc = new \ReflectionClass(PrePersistEvent::class);

        $this->assertTrue($rc->isSubclassOf(Event::class));
    }

    public function testShouldFinal()
    {
        $rc = new \ReflectionClass(PrePersistEvent::class);

        $this->assertTrue($rc->isFinal());
    }

    public function testCouldBeConstructedWithPagerAndObjectPersisterAndOptions()
    {
        new PrePersistEvent($this->createPagerMock(), $this->createObjectPersisterMock(), []);
    }

    public function testShouldAllowGetPagerSetInConstructor()
    {
        $expectedPager = $this->createPagerMock();

        $event = new PrePersistEvent($expectedPager, $this->createObjectPersisterMock(), []);

        $this->assertSame($expectedPager, $event->getPager());
    }

    public function testShouldAllowGetPreviouslySetPager()
    {
        $event = new PrePersistEvent($this->createPagerMock(), $this->createObjectPersisterMock(), []);

        $expectedPager = $this->createPagerMock();
        $event->setPager($expectedPager);

        $this->assertSame($expectedPager, $event->getPager());
    }

    public function testShouldAllowGetObjectPersisterSetInConstructor()
    {
        $expectedPersister = $this->createObjectPersisterMock();

        $event = new PrePersistEvent($this->createPagerMock(), $expectedPersister, []);

        $this->assertSame($expectedPersister, $event->getObjectPersister());
    }

    public function testShouldAllowGetPreviouslySetObjectsPersister()
    {
        $event = new PrePersistEvent($this->createPagerMock(), $this->createObjectPersisterMock(), []);

        $expectedPersister = $this->createObjectPersisterMock();
        $event->setObjectPersister($expectedPersister);

        $this->assertSame($expectedPersister, $event->getObjectPersister());
    }

    public function testShouldAllowGetOptionsSetInConstructor()
    {
        $expectedOptions = ['foo' => 'fooVal', 'bar' => 'barVal'];

        $event = new PrePersistEvent($this->createPagerMock(), $this->createObjectPersisterMock(), $expectedOptions);

        $this->assertSame($expectedOptions, $event->getOptions());
    }

    public function testShouldAllowGetPreviouslySetOptions()
    {
        $event = new PrePersistEvent($this->createPagerMock(), $this->createObjectPersisterMock(), ['foo' => 'fooVal', 'bar' => 'barVal']);

        $expectedOptions = ['foo' => 'fooNewVal', 'bar' => 'barnewVal'];
        $event->setOptions($expectedOptions);

        $this->assertSame($expectedOptions, $event->getOptions());
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
