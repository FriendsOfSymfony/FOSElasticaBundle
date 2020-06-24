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
    private $mapping;

    protected function setUp(): void
    {
        $this->mapping = [
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
        ];
        $this->typeConfig = new TypeConfig('_doc', [
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
        ]);
        $this->builder = new MappingBuilder();
    }

    public function testMappingBuilderStoreProperty()
    {
        $mapping = $this->builder->buildTypeMapping(null, $this->typeConfig);

        $this->assertArrayNotHasKey('store', $mapping['properties']['storeless']);
        $this->assertArrayHasKey('store', $mapping['properties']['stored']);
        $this->assertTrue($mapping['properties']['stored']['store']);
        $this->assertArrayHasKey('store', $mapping['properties']['unstored']);
        $this->assertFalse($mapping['properties']['unstored']['store']);
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
                'mappings' => $this->mapping
            ],
            $this->builder->buildIndexTemplateMapping($config)
        );
    }
}
