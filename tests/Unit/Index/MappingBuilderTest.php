<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Index;

use FOS\ElasticaBundle\Configuration\IndexTemplateConfig;
use FOS\ElasticaBundle\Configuration\TypeConfig;
use FOS\ElasticaBundle\Index\MappingBuilder;
use PHPUnit\Framework\TestCase;

class MappingBuilderTest extends TestCase
{
    /**
     * @var TypeConfig
     */
    private $typeConfig;

    /**
     * @var MappingBuilder
     */
    private $builder;

    /**
     * @var array
     */
    private $typeMapping;

    protected function setUp()
    {
        $this->typeMapping = [
            'properties' => [
                'storeless' => [
                    'type' => 'text',
                ],
                'stored' => [
                    'type' => 'text',
                    'store' => true,
                ],
                'unstored' => [
                    'type' => 'text',
                    'store' => false,
                ],
            ],
            '_parent' => [
                'type' => 'parent_type',
            ],
        ];
        $this->typeConfig = new TypeConfig('typename', [
            'properties' => [
                'storeless' => [
                    'type' => 'text',
                ],
                'stored' => [
                    'type' => 'text',
                    'store' => true,
                ],
                'unstored' => [
                    'type' => 'text',
                    'store' => false,
                ],
            ],
            '_parent' => [
                'type' => 'parent_type',
                'identifier' => 'name',
                'property' => 'parent_property',
            ],
        ]);
        $this->builder = new MappingBuilder();
    }

    public function testMappingBuilderStoreProperty()
    {
        $mapping = $this->builder->buildTypeMapping($this->typeConfig);

        $this->assertArrayNotHasKey('store', $mapping['properties']['storeless']);
        $this->assertArrayHasKey('store', $mapping['properties']['stored']);
        $this->assertTrue($mapping['properties']['stored']['store']);
        $this->assertArrayHasKey('store', $mapping['properties']['unstored']);
        $this->assertFalse($mapping['properties']['unstored']['store']);

        $this->assertArrayHasKey('_parent', $mapping);
        $this->assertArrayNotHasKey('identifier', $mapping['_parent']);
        $this->assertArrayNotHasKey('property', $mapping['_parent']);
    }

    public function testBuildIndexTemplateMapping()
    {
        $config = new IndexTemplateConfig(
            'some_template',
            [
                $this->typeConfig
            ],
            ['template' => 'index_template_*']
        );
        $this->assertEquals(
            [
                'template' => 'index_template_*',
                'mappings' => [
                    'typename' => $this->typeMapping
                ]
            ],
            $this->builder->buildIndexTemplateMapping($config)
        );
    }
}
