<?php

namespace FOS\ElasticaBundle\Tests\EventListener;

use FOS\ElasticaBundle\Event\PopulateEvent;
use FOS\ElasticaBundle\EventListener\PopulateListener;
use PHPUnit_Framework_MockObject_MockObject;

class PopulateListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $resetter;

    /**
     * @var PopulateListener
     */
    private $listener;

    protected function setUp()
    {
        $this->resetter = $this->getMockBuilder('FOS\ElasticaBundle\Index\Resetter')
            ->disableOriginalConstructor()
            ->getMock();

        $this->listener = new PopulateListener($this->resetter);
    }

    public function testPostIndexPopulate()
    {
        $this->resetter->expects($this->once())->method('postPopulate')->with('indexName');
        $this->listener->postIndexPopulate(new PopulateEvent('indexName', null, true, array()));
    }

    public function preIndexPopulateDataProvider()
    {
        return array(
            array(
                array(
                    array('resetIndex', $this->never()),
                    array('resetIndexType', $this->never())
                ),
                array('indexName', true),
                new PopulateEvent('indexName', null, false, array())
            ),
            array(
                array(
                    array('resetIndex', $this->once())
                ),
                array('indexName', true),
                new PopulateEvent('indexName', null, true, array())
            ),
            array(
                array(
                    array('resetIndexType', $this->once())
                ),
                array('indexName', 'indexType'),
                new PopulateEvent('indexName', 'indexType', true, array())
            )
        );
    }

    /**
     * @param array         $asserts
     * @param array         $withArgs
     * @param PopulateEvent $event
     *
     * @dataProvider preIndexPopulateDataProvider
     */
    public function testPreIndexPopulate(array $asserts, array $withArgs, PopulateEvent $event)
    {
        foreach ($asserts as $assert) {
            $this->resetter->expects($assert[1])->method($assert[0])->with($withArgs[0], $withArgs[1]);
        }

        $this->listener->preIndexPopulate($event);
    }
}
