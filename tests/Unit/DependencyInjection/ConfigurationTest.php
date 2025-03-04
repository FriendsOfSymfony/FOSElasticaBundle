<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\DependencyInjection;

use FOS\ElasticaBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

/**
 * @internal
 */
class ConfigurationTest extends TestCase
{
    /**
     * @var Processor
     */
    private $processor;

    protected function setUp(): void
    {
        $this->processor = new Processor();
    }

    public function testUnconfiguredConfiguration()
    {
        $configuration = $this->getConfigs([]);

        $this->assertSame([
            'clients' => [],
            'indexes' => [],
            'index_templates' => [],
            'default_manager' => 'orm',
            'messenger' => [
                'enabled' => false,
                'bus' => 'messenger.default_bus',
            ],
        ], $configuration);
    }

    public function testClientConfiguration()
    {
        $configuration = $this->getConfigs([
            'clients' => [
                'default' => [
                    'hosts' => ['http://localhost:9200'],
                    'retry_on_conflict' => 5,
                ],
                'clustered' => [
                    'hosts' => [
                        'http://es1:9200',
                        'http://es2:9200',
                    ],
                    'headers' => [
                        'Authorization: Basic QWxhZGRpbjpvcGVuIHNlc2FtZQ==',
                    ],
                ],
            ],
        ]);

        $this->assertCount(2, $configuration['clients']);
        $this->assertCount(1, $configuration['clients']['default']['hosts']);
        $this->assertCount(0, $configuration['clients']['default']['headers']);
        $this->assertSame(5, $configuration['clients']['default']['retry_on_conflict']);

        $this->assertCount(2, $configuration['clients']['clustered']['hosts']);
        $this->assertSame('http://es2:9200/', $configuration['clients']['clustered']['hosts'][1]);
        $this->assertCount(1, $configuration['clients']['clustered']['headers']);
        $this->assertSame('Authorization: Basic QWxhZGRpbjpvcGVuIHNlc2FtZQ==', $configuration['clients']['clustered']['headers'][0]);
    }

    public function testLogging()
    {
        $configuration = $this->getConfigs([
            'clients' => [
                'logging_enabled' => [
                    'hosts' => ['http://localhost:9200'],
                    'logger' => true,
                ],
                'logging_disabled' => [
                    'hosts' => ['http://localhost:9200'],
                    'logger' => false,
                ],
                'logging_not_mentioned' => [
                    'hosts' => ['http://localhost:9200'],
                ],
                'logging_custom' => [
                    'hosts' => ['http://localhost:9200'],
                    'logger' => 'custom.service',
                ],
            ],
        ]);

        $this->assertCount(4, $configuration['clients']);

        $this->assertSame('fos_elastica.logger', $configuration['clients']['logging_enabled']['logger']);
        $this->assertFalse($configuration['clients']['logging_disabled']['logger']);
        $this->assertSame('fos_elastica.logger', $configuration['clients']['logging_not_mentioned']['logger']);
        $this->assertSame('custom.service', $configuration['clients']['logging_custom']['logger']);
    }

    public function testSlashIsAddedAtTheEndOfServerUrl()
    {
        $config = [
            'clients' => [
                'default' => ['hosts' => ['http://www.github.com']],
            ],
        ];
        $configuration = $this->getConfigs($config);

        $this->assertSame('http://www.github.com/', $configuration['clients']['default']['hosts'][0]);
    }

    public function testIndexConfig()
    {
        $this->getConfigs([
            'clients' => [
                'default' => ['hosts' => ['http://localhost:9200']],
            ],
            'indexes' => [
                'test' => [
                    'index_prototype' => [
                        'analyzer' => 'custom_analyzer',
                        'persistence' => [
                            'identifier' => 'ID',
                        ],
                        'serializer' => [
                            'groups' => ['Search'],
                            'version' => 1,
                            'serialize_null' => false,
                        ],
                    ],
                    'persistence' => [
                        'listener' => [
                            'logger' => true,
                        ],
                    ],
                    'properties' => [
                        'title' => [],
                        'published' => ['type' => 'datetime'],
                        'body' => null,
                    ],
                ],
            ],
        ]);
    }

    public function testUnconfiguredIndex()
    {
        $configuration = $this->getConfigs([
            'clients' => [
                'default' => ['hosts' => ['http://localhost:9200']],
            ],
            'indexes' => [
                'test' => null,
            ],
        ]);

        $this->assertArrayHasKey('properties', $configuration['indexes']['test']);
    }

    public function testNestedProperties()
    {
        $this->getConfigs([
            'clients' => [
                'default' => ['hosts' => ['http://localhost:9200']],
            ],
            'indexes' => [
                'test' => [
                    'persistence' => [],
                    'properties' => [
                        'field1' => [],
                        'field2' => [
                            'type' => 'nested',
                            'properties' => [
                                'nested_field1' => [
                                    'type' => 'integer',
                                ],
                                'nested_field2' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'id' => [
                                            'type' => 'integer',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function testTimeoutConfig()
    {
        $configuration = $this->getConfigs([
            'clients' => [
                'simple_timeout' => [
                    'hosts' => ['http://localhost:9200'],
                    'timeout' => 123,
                ],
            ],
        ]);

        $this->assertSame(123, $configuration['clients']['simple_timeout']['timeout']);
    }

    public function testHttpErrorCodesConfig()
    {
        // test defaults
        $configuration = $this->getConfigs([
            'clients' => [
                'default' => [
                ],
            ],
        ]);
        $connection = $configuration['clients']['default'];
        $this->assertSame([400, 403], $connection['http_error_codes']);

        // test custom
        $configuration = $this->getConfigs([
            'clients' => [
                'default' => [
                    'http_error_codes' => ['HTTP_ERROR_CODE'],
                ],
            ],
        ]);
        $connection = $configuration['clients']['default'];
        $this->assertSame(['HTTP_ERROR_CODE'], $connection['http_error_codes']);
    }

    public function testIndexTemplates()
    {
        $configuration = $this->getConfigs(
            [
                'index_templates' => [
                    'some_template' => [
                        'index_patterns' => ['some_template_*'],
                        'client' => 'default',
                        'properties' => [
                            'some_field' => [],
                        ],
                    ],
                ],
            ]
        );
        $indexTemplate = $configuration['index_templates']['some_template'];
        $this->assertSame(['some_template_*'], $indexTemplate['index_patterns']);
        $this->assertSame('default', $indexTemplate['client']);
        $this->assertSame([], $indexTemplate['settings']);
        $this->assertArrayHasKey('some_field', $indexTemplate['properties']);
    }

    private function getConfigs(array $configArray)
    {
        $configuration = new Configuration(true);

        return $this->processor->processConfiguration($configuration, [$configArray]);
    }
}
