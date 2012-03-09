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
                        'a' => $this->getMockElasticaTypeMapping(),
                        'b' => $this->getMockElasticaTypeMapping(),
                    ),
                ),
            ),
            'bar' => array(
                'index' => $this->getMockElasticaIndex(),
                'config' => array(
                    'mappings' => array(
                        'a' => $this->getMockElasticaTypeMapping(),
                        'b' => $this->getMockElasticaTypeMapping(),
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

        $type->expects($this->once())
            ->method('setMapping')
            ->with($this->indexConfigsByName['foo']['config']['mappings']['a']);

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

    /**
     * @return Elastica_Type_Mapping
     */
    private function getMockElasticaTypeMapping()
    {
        return $this->getMockBuilder('Elastica_Type_Mapping')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
