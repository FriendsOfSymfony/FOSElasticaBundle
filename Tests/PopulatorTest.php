<?php

namespace FOQ\ElasticaBundle\Tests\Populator;

use FOQ\ElasticaBundle\Populator;
use FOQ\ElasticaBundle\Provider\ProviderInterface;
use Closure;

class PopulatorMock extends Populator
{
    public $providers = array();
}

class PopulatorTest extends \PHPUnit_Framework_TestCase
{
    public function testThatWeCanAddProvider()
    {
        $provider = $this->getMock('FOQ\ElasticaBundle\Provider\ProviderInterface', array('populate'));
  
        $populator = new PopulatorMock(array());
        $populator->addProvider('l3l0Provider', $provider);

        $this->assertEquals(count($populator->providers), 1);
        $this->assertArrayHasKey('l3l0Provider', $populator->providers);
        $this->assertInstanceOf('FOQ\ElasticaBundle\Provider\ProviderInterface', $populator->providers['l3l0Provider']);
    }
  
    public function testThatPopulateThroughProviders()
    {
        $provider = $this->getMock('FOQ\ElasticaBundle\Provider\ProviderInterface', array('populate'));
        $provider->expects($this->once())
          ->method('populate');
  
        $populator = new Populator(array('l3l0Provider' => $provider));
        $populator->populate(function ($text) { return $text; });
    }
}
