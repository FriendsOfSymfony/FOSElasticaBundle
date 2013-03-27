<?php

namespace FOS\ElasticaBundle\Tests\MappingRegistry;

use FOS\ElasticaBundle\MappingRegistry;
use Elastica_Type;
use Elastica_Index;

class MappingRegistryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
       if (!class_exists('Elastica_Type') || !class_exists('Elastica_Index')) {
           $this->markTestSkipped('The Elastica library classes are not available');
       }
    }

    public function testThatCanApplyMappings()
    {
        $typeMock = $this->getMockBuilder('Elastica_Type')
            ->disableOriginalConstructor()
            ->getMock();
        
        $typeMock->expects($this->once())
            ->method('setMapping')
            ->with($this->equalTo(array('mappingArray')));
        
        $mapping = new MappingRegistry(array(
            'index/type' => array($typeMock, array('mappingArray'))
        ));

        $mapping->applyMappings();
    }
   
    /**
     * @dataProvider invalidTypesParametersProvider 
     * @expectedException InvalidArgumentException
     */
    public function testThatCannotGetTypeFieldForTypeWhichNotExists($indexName, $typeName)
    {
        $type = $this->getTypeMock('index', 'type');
        $mapping = new MappingRegistry(array(
            'index/type' => array($type, array('mappingArray'))
        ));
      
        $mapping->getTypeFieldNames($this->getTypeMock($indexName, $typeName));
    }
    
    public function testThatCanGetTypeField()
    {
        $type = $this->getTypeMock('index', 'type');
        $mapping = new MappingRegistry(array(
            'index/type' => array($type, array('mappingArray'))
        ));
      
        $mapping->getTypeFieldNames($this->getTypeMock('index', 'type'));
    }
    
    public static function invalidTypesParametersProvider()
    {
      return array(
        array('index1', 'type'),
        array('index', 'type2')
      );
    }

    private function getTypeMock($indexName, $typeName)
    {
        $typeMock = $this->getMockBuilder('Elastica_Type')
            ->disableOriginalConstructor()
            ->getMock();
        
        $indexMock = $this->getMockBuilder('Elastica_Index')
            ->disableOriginalConstructor()
            ->getMock();
       
        $indexMock->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($indexName));
        
        $typeMock->expects($this->any())
            ->method('getIndex')
            ->will($this->returnValue($indexMock));
        
        $typeMock->expects($this->any())
            ->method('getType')
            ->will($this->returnValue($typeName));

        return $typeMock;
    }
}
