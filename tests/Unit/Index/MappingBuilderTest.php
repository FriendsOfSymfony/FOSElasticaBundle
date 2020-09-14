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

use FOS\ElasticaBundle\Configuration\IndexConfig;
use FOS\ElasticaBundle\Configuration\IndexTemplateConfig;
use FOS\ElasticaBundle\Index\MappingBuilder;
use PHPUnit\Framework\TestCase;

class MappingBuilderTest extends TestCase
{
    /**
     * @var MappingBuilder
     */
    private $builder;

    /**
     * @var array
     */
    private $mapping;

    /**
     * @var IndexConfig
     */
    private $indexConfig;

    protected function setUp(): void
    {
        $this->mapping = [
            'mapping' => [
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
            ],
        ];
        $this->indexConfig = new IndexConfig(
            [
                'name' => 'name',
                'config' => [],
                'model' => null,
                'mapping' => [
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
                ],
            ]
        );
        $this->builder = new MappingBuilder();
    }

    public function testMappingBuilderStoreProperty()
    {
        $mapping = $this->builder->buildMapping(null, $this->indexConfig);

        $this->assertArrayNotHasKey('store', $mapping['properties']['storeless']);
        $this->assertArrayHasKey('store', $mapping['properties']['stored']);
        $this->assertTrue($mapping['properties']['stored']['store']);
        $this->assertArrayHasKey('store', $mapping['properties']['unstored']);
        $this->assertFalse($mapping['properties']['unstored']['store']);
    }

    public function testBuildIndexTemplateMapping()
    {
        $config = new IndexTemplateConfig(
            ['template' => 'index_template_*', 'name' => 'some_template', 'config' => [], 'mapping' => $this->indexConfig->getMapping()]
        );
        $this->assertEquals(
            [
                'template' => 'index_template_*',
                'mappings' => $this->indexConfig->getMapping(),
            ],
            $this->builder->buildIndexTemplateMapping($config)
        );
    }
}
