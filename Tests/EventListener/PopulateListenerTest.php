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

    public function testPreIndexPopulateWhenNoResetRequired()
    {
        $this->resetter->expects($this->never())->method('resetIndex');
        $this->resetter->expects($this->never())->method('resetIndexType');
        $this->listener->preIndexPopulate(new PopulateEvent('indexName', null, false, array()));
    }

    public function testPreIndexPopulateWhenResetIsRequiredAndNoTypeIsSpecified()
    {
        $this->resetter->expects($this->once())->method('resetIndex')->with('indexName');
        $this->listener->preIndexPopulate(new PopulateEvent('indexName', null, true, array()));
    }

    public function testPreIndexPopulateWhenResetIsRequiredAndTypeIsSpecified()
    {
        $this->resetter->expects($this->once())->method('resetIndexType')->with('indexName', 'indexType');
        $this->listener->preIndexPopulate(new PopulateEvent('indexName', 'indexType', true, array()));
    }
}
