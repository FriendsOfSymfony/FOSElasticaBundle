<?php

namespace FOS\ElasticaBundle\Tests\Index;

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

}
