<?php

namespace FOS\ElasticaBundle\Tests\Populator;

use FOS\ElasticaBundle\Populator;
use FOS\ElasticaBundle\Provider\ProviderInterface;
use Closure;

class PopulatorMock extends Populator
{
    public $providers = array();
}

class PopulatorTest extends \PHPUnit_Framework_TestCase
{
    public function testThatWeCanAddProvider()
    {
        $provider = $this->getMock('FOS\ElasticaBundle\Provider\ProviderInterface', array('populate'));
  
        $populator = new PopulatorMock(array());
        $populator->addProvider('l3l0Provider', $provider);

        $this->assertEquals(count($populator->providers), 1);
        $this->assertArrayHasKey('l3l0Provider', $populator->providers);
        $this->assertInstanceOf('FOS\ElasticaBundle\Provider\ProviderInterface', $populator->providers['l3l0Provider']);
    }
  
    public function testThatPopulateThroughProviders()
    {
        $provider = $this->getMock('FOS\ElasticaBundle\Provider\ProviderInterface', array('populate'));
        $provider->expects($this->once())
            ->method('populate');
        
        $provider2 = $this->getMock('FOS\ElasticaBundle\Provider\ProviderInterface', array('populate'));
        $provider2->expects($this->once())
            ->method('populate');
  
        $populator = new Populator(array('l3l0Provider' => $provider, 'secondProvider' => $provider2));
        $populator->populate(function ($text) { return $text; });
    }

   /**
    * @expectedException PHPUnit_Framework_Error
    */
    public function testThatAddProviderHaveToImpelementProviderInterface()
    {
        $populator = new Populator(array());
        $populator->addProvider('provider', new \stdClass());
        $populator->populate(function ($text) { return $text; });
    }
   
   /**
    * @expectedException PHPUnit_Framework_Error
    */
    public function testThatProvidersPassToTheContructorHaveToImplementProviderInterface()
    {
        $populator = new Populator(array('provider' => new \stdClass()));
        $populator->populate(function ($text) { return $text; });
    }
}
