<?php

namespace FOQ\ElasticaBundle\Tests\Resetter;

use FOQ\ElasticaBundle\Resetter;

class ResetterTest extends \PHPUnit_Framework_TestCase
{
    private $indexConfigsByName;

    public function setUp()
    {
        $this->indexConfigsByName = array(
            'foo' => array(
                'index' => $this->getMockElasticaIndex(),
                'config' => array(
                    'mappings' => array(
                        'a' => array('properties' => array()),
                        'b' => array('properties' => array()),
                    ),
                ),
            ),
            'bar' => array(
                'index' => $this->getMockElasticaIndex(),
                'config' => array(
                    'mappings' => array(
                        'a' => array('properties' => array()),
                        'b' => array('properties' => array()),
                    ),
                ),
            ),
            'parent' => array(
                'index' => $this->getMockElasticaIndex(),
                'config' => array(
                    'mappings' => array(
                        'a' => array('properties' => array(
                                'field_1' => array('_parent' => array('type' => 'b', 'identifier' => 'id')),
                                'field_2' => array())),
                        'b' => array('properties' => array()),
                    ),
                ),
            ),
        );
    }

    public function testResetAllIndexes()
    {
        $this->indexConfigsByName['foo']['index']->expects($this->once())
            ->method('create')
            ->with($this->indexConfigsByName['foo']['config'], true);

        $this->indexConfigsByName['bar']['index']->expects($this->once())
            ->method('create')
            ->with($this->indexConfigsByName['bar']['config'], true);

        $resetter = new Resetter($this->indexConfigsByName);
        $resetter->resetAllIndexes();
    }

    public function testResetIndex()
    {
        $this->indexConfigsByName['foo']['index']->expects($this->once())
            ->method('create')
            ->with($this->indexConfigsByName['foo']['config'], true);

        $this->indexConfigsByName['bar']['index']->expects($this->never())
            ->method('create');

        $resetter = new Resetter($this->indexConfigsByName);
        $resetter->resetIndex('foo');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testResetIndexShouldThrowExceptionForInvalidIndex()
    {
        $resetter = new Resetter($this->indexConfigsByName);
        $resetter->resetIndex('baz');
    }

    public function testResetIndexType()
    {
        $type = $this->getMockElasticaType();

        $this->indexConfigsByName['foo']['index']->expects($this->once())
            ->method('getType')
            ->with('a')
            ->will($this->returnValue($type));

        $type->expects($this->once())
            ->method('delete');

        $mapping = \Elastica_Type_Mapping::create($this->indexConfigsByName['foo']['config']['mappings']['a']['properties']);
        $type->expects($this->once())
            ->method('setMapping')
            ->with($mapping);

        $resetter = new Resetter($this->indexConfigsByName);
        $resetter->resetIndexType('foo', 'a');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testResetIndexTypeShouldThrowExceptionForInvalidIndex()
    {
        $resetter = new Resetter($this->indexConfigsByName);
        $resetter->resetIndexType('baz', 'a');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testResetIndexTypeShouldThrowExceptionForInvalidType()
    {
        $resetter = new Resetter($this->indexConfigsByName);
        $resetter->resetIndexType('foo', 'c');
    }

    public function testIndexMappingForParent()
    {
        $type = $this->getMockElasticaType();

        $this->indexConfigsByName['parent']['index']->expects($this->once())
            ->method('getType')
            ->with('a')
            ->will($this->returnValue($type));

        $type->expects($this->once())
            ->method('delete');

        $mapping = \Elastica_Type_Mapping::create($this->indexConfigsByName['parent']['config']['mappings']['a']['properties']);
        $mapping->setParam('_parent', array('type' => 'b'));
        $type->expects($this->once())
            ->method('setMapping')
            ->with($mapping);

        $resetter = new Resetter($this->indexConfigsByName);
        $resetter->resetIndexType('parent', 'a');
    }

    /**
     * @return Elastica_Index
     */
    private function getMockElasticaIndex()
    {
        return $this->getMockBuilder('Elastica_Index')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return Elastica_Type
     */
    private function getMockElasticaType()
    {
        return $this->getMockBuilder('Elastica_Type')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
