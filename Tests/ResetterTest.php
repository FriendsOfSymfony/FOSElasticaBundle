<?php

namespace FOS\ElasticaBundle\Tests\Resetter;

use Elastica\Exception\ResponseException;
use Elastica\Request;
use Elastica\Response;
use FOS\ElasticaBundle\Resetter;
use Elastica\Type\Mapping;

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
                        'a' => array(
                            'dynamic_templates' => array(),
                            'properties' => array(),
                        ),
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
                        'a' => array(
                            'properties' => array(
                                'field_2' => array()
                            ),
                            '_parent' => array(
                                'type' => 'b',
                                'property' => 'b',
                                'identifier' => 'id'
                            ),
                        ),
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
     * @expectedException \InvalidArgumentException
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

        $mapping = Mapping::create($this->indexConfigsByName['foo']['config']['mappings']['a']['properties']);
        $mapping->setParam('dynamic_templates', $this->indexConfigsByName['foo']['config']['mappings']['a']['dynamic_templates']);
        $type->expects($this->once())
            ->method('setMapping')
            ->with($mapping);

        $resetter = new Resetter($this->indexConfigsByName);
        $resetter->resetIndexType('foo', 'a');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testResetIndexTypeShouldThrowExceptionForInvalidIndex()
    {
        $resetter = new Resetter($this->indexConfigsByName);
        $resetter->resetIndexType('baz', 'a');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testResetIndexTypeShouldThrowExceptionForInvalidType()
    {
        $resetter = new Resetter($this->indexConfigsByName);
        $resetter->resetIndexType('foo', 'c');
    }

    public function testResetIndexTypeIgnoreTypeMissingException()
    {
        $type = $this->getMockElasticaType();

        $this->indexConfigsByName['foo']['index']->expects($this->once())
            ->method('getType')
            ->with('a')
            ->will($this->returnValue($type));

        $type->expects($this->once())
            ->method('delete')
            ->will($this->throwException(new ResponseException(
                new Request(''),
                new Response(array('error' => 'TypeMissingException[[de_20131022] type[bla] missing]', 'status' => 404)))
            ));

        $mapping = Mapping::create($this->indexConfigsByName['foo']['config']['mappings']['a']['properties']);
        $mapping->setParam('dynamic_templates', $this->indexConfigsByName['foo']['config']['mappings']['a']['dynamic_templates']);
        $type->expects($this->once())
            ->method('setMapping')
            ->with($mapping);

        $resetter = new Resetter($this->indexConfigsByName);
        $resetter->resetIndexType('foo', 'a');
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

        $mapping = Mapping::create($this->indexConfigsByName['parent']['config']['mappings']['a']['properties']);
        $mapping->setParam('_parent', array('type' => 'b'));
        $type->expects($this->once())
            ->method('setMapping')
            ->with($mapping);

        $resetter = new Resetter($this->indexConfigsByName);
        $resetter->resetIndexType('parent', 'a');
    }

    /**
     * @return \Elastica\Index
     */
    private function getMockElasticaIndex()
    {
        return $this->getMockBuilder('Elastica\Index')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \Elastica\Type
     */
    private function getMockElasticaType()
    {
        return $this->getMockBuilder('Elastica\Type')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
