<?php

namespace FOS\ElasticaBundle\Tests\Index;

use FOS\ElasticaBundle\Configuration\IndexTemplateConfig;
use FOS\ElasticaBundle\Configuration\TypeConfig;
use FOS\ElasticaBundle\Index\MappingBuilder;

class MappingBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MappingBuilder
     */
    private $builder;

    protected function setUp()
    {
        $this->builder = new MappingBuilder();
    }

    public function testMappingBuilderStoreProperty()
    {
        $typeConfig = new TypeConfig('typename', array(
            'properties' => array(
                'storeless' => array(
                    'type' => 'string'
                ),
                'stored' => array(
                    'type' => 'string',
                    'store' => true
                ),
                'unstored' => array(
                    'type' => 'string',
                    'store' => false
                ),
            )
        ));

        $mapping = $this->builder->buildTypeMapping($typeConfig);

        $this->assertArrayNotHasKey('store', $mapping['properties']['storeless']);
        $this->assertArrayHasKey('store', $mapping['properties']['stored']);
        $this->assertTrue($mapping['properties']['stored']['store']);
        $this->assertArrayHasKey('store', $mapping['properties']['unstored']);
        $this->assertFalse($mapping['properties']['unstored']['store']);
    }

    public function testBuildIndexTemplateMapping()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|IndexTemplateConfig $indexTemplateConfig */
        $indexTemplateConfig = $this->getMockBuilder('\FOS\ElasticaBundle\Configuration\IndexTemplateConfig')
            ->disableOriginalConstructor()
            ->setMethods(array('getTypes', 'getSettings', 'getTemplate'))
            ->getMock();
        $indexTemplateConfig->expects($this->once())
            ->method('getTypes')
            ->willReturn(array(new TypeConfig('type1', array('properties' => array()))));
        $indexTemplateConfig->expects($this->once())
            ->method('getSettings')
            ->willReturn(array('1'));
        $indexTemplateConfig->expects($this->once())
            ->method('getTemplate')
            ->willReturn('t*');
        $mappingBuilder = new MappingBuilder();
        $mapping = $mappingBuilder->buildIndexTemplateMapping($indexTemplateConfig);
        $this->assertEquals(
            array(
                'mappings' => array('type1' => new \stdClass()),
                'settings' => array(1),
                'template' => 't*',
            ),
            $mapping
        );
    }

}
