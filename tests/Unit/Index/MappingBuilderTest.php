<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Index;

use FOS\ElasticaBundle\Configuration\IndexConfig;
use FOS\ElasticaBundle\Configuration\IndexTemplateConfig;
use FOS\ElasticaBundle\Elastica\ElasticsearchVersionDetector;
use FOS\ElasticaBundle\Index\MappingBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
class MappingBuilderTest extends TestCase
{
    private MappingBuilder $builder;

    private IndexConfig $indexConfig;

    protected function setUp(): void
    {
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

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->builder = new MappingBuilder($dispatcher);
    }

    public function testMappingBuilderStoreProperty(): void
    {
        $mapping = $this->builder->buildMapping(null, $this->indexConfig);

        $this->assertArrayNotHasKey('store', $mapping['properties']['storeless']);
        $this->assertArrayHasKey('store', $mapping['properties']['stored']);
        $this->assertTrue($mapping['properties']['stored']['store']);
        $this->assertArrayHasKey('store', $mapping['properties']['unstored']);
        $this->assertFalse($mapping['properties']['unstored']['store']);
    }

    public function testBuildIndexTemplateMapping(): void
    {
        $config = new IndexTemplateConfig(
            ['index_patterns' => ['index_template_*'], 'name' => 'some_template', 'config' => [], 'mapping' => $this->indexConfig->getMapping()]
        );

        $expected = ElasticsearchVersionDetector::usesNewIndexTemplateApi()
            ? [
                'template' => [
                    'mappings' => $this->indexConfig->getMapping(),
                ],
                'index_patterns' => ['index_template_*'],
            ]
            : [
                'index_patterns' => ['index_template_*'],
                'mappings' => $this->indexConfig->getMapping(),
            ];

        $this->assertEquals(
            $expected,
            $this->builder->buildIndexTemplateMapping($config)
        );
    }
}
