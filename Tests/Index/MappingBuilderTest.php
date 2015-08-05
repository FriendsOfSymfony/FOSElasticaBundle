<?php
/*
 * This file is part of the OpCart software.
 *
 * (c) 2015, OpticsPlanet, Inc
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Index;


use FOS\ElasticaBundle\Configuration\IndexTemplateConfig;
use FOS\ElasticaBundle\Configuration\TypeConfig;
use FOS\ElasticaBundle\Index\MappingBuilder;

class MappingBuilderTest extends \PHPUnit_Framework_TestCase
{
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