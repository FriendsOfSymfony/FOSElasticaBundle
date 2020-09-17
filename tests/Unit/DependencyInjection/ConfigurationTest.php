<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Resetter\DependencyInjection;

use FOS\ElasticaBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

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
        ], $configuration);
    }

    public function testClientConfiguration()
    {
        $configuration = $this->getConfigs([
            'clients' => [
                'default' => [
                    'url' => 'http://localhost:9200',
                    'retryOnConflict' => 5,
                ],
                'clustered' => [
                    'connections' => [
                        [
                            'url' => 'http://es1:9200',
                            'headers' => [
                                'Authorization: Basic QWxhZGRpbjpvcGVuIHNlc2FtZQ==',
                            ],
                        ],
                        [
                            'url' => 'http://es2:9200',
                            'headers' => [
                                'Authorization: Basic QWxhZGRpbjpvcGVuIHNlc2FtZQ==',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertCount(2, $configuration['clients']);
        $this->assertCount(1, $configuration['clients']['default']['connections']);
        $this->assertCount(0, $configuration['clients']['default']['connections'][0]['headers']);
        $this->assertSame(5, $configuration['clients']['default']['connections'][0]['retryOnConflict']);

        $this->assertCount(2, $configuration['clients']['clustered']['connections']);
        $this->assertSame('http://es2:9200/', $configuration['clients']['clustered']['connections'][1]['url']);
        $this->assertCount(1, $configuration['clients']['clustered']['connections'][1]['headers']);
        $this->assertSame('Authorization: Basic QWxhZGRpbjpvcGVuIHNlc2FtZQ==', $configuration['clients']['clustered']['connections'][0]['headers'][0]);
    }

    public function testLogging()
    {
        $configuration = $this->getConfigs([
            'clients' => [
                'logging_enabled' => [
                    'url' => 'http://localhost:9200',
                    'logger' => true,
                ],
                'logging_disabled' => [
                    'url' => 'http://localhost:9200',
                    'logger' => false,
                ],
                'logging_not_mentioned' => [
                    'url' => 'http://localhost:9200',
                ],
                'logging_custom' => [
                    'url' => 'http://localhost:9200',
                    'logger' => 'custom.service',
                ],
            ],
        ]);

        $this->assertCount(4, $configuration['clients']);

        $this->assertSame('fos_elastica.logger', $configuration['clients']['logging_enabled']['connections'][0]['logger']);
        $this->assertFalse($configuration['clients']['logging_disabled']['connections'][0]['logger']);
        $this->assertSame('fos_elastica.logger', $configuration['clients']['logging_not_mentioned']['connections'][0]['logger']);
        $this->assertSame('custom.service', $configuration['clients']['logging_custom']['connections'][0]['logger']);
    }

    public function testSlashIsAddedAtTheEndOfServerUrl()
    {
        $config = [
            'clients' => [
                'default' => ['url' => 'http://www.github.com'],
            ],
        ];
        $configuration = $this->getConfigs($config);

        $this->assertSame('http://www.github.com/', $configuration['clients']['default']['connections'][0]['url']);
    }

    public function testIndexConfig()
    {
        $this->getConfigs([
            'clients' => [
                'default' => ['url' => 'http://localhost:9200'],
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

    public function testClientConfigurationNoUrl()
    {
        $configuration = $this->getConfigs([
            'clients' => [
                'default' => [
                    'host' => 'localhost',
                    'port' => 9200,
                ],
            ],
        ]);

        $this->assertTrue(empty($configuration['clients']['default']['connections'][0]['url']));
    }

    public function testUnconfiguredType()
    {
        $configuration = $this->getConfigs([
                'clients' => [
                    'default' => ['url' => 'http://localhost:9200'],
                ],
                'indexes' => [
                    'test' => [
                    ],
                ],
            ]);

        $this->assertArrayHasKey('properties', $configuration['indexes']['test']);
    }

    public function testNestedProperties()
    {
        $this->getConfigs([
            'clients' => [
                'default' => ['url' => 'http://localhost:9200'],
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

    public function testCompressionConfig()
    {
        $configuration = $this->getConfigs([
            'clients' => [
                'compression_enabled' => [
                    'compression' => true,
                ],
                'compression_disabled' => [
                    'compression' => false,
                ],
            ],
        ]);

        $this->assertTrue($configuration['clients']['compression_enabled']['connections'][0]['compression']);
        $this->assertFalse($configuration['clients']['compression_disabled']['connections'][0]['compression']);
    }

    public function testCompressionDefaultConfig()
    {
        $configuration = $this->getConfigs([
            'clients' => [
                'default' => [],
            ],
        ]);

        $this->assertFalse($configuration['clients']['default']['connections'][0]['compression']);
    }

    public function testTimeoutConfig()
    {
        $configuration = $this->getConfigs([
            'clients' => [
                'simple_timeout' => [
                    'url' => 'http://localhost:9200',
                    'timeout' => 123,
                ],
                'connect_timeout' => [
                    'url' => 'http://localhost:9200',
                    'connectTimeout' => 234,
                ],
            ],
        ]);

        $this->assertSame(123, $configuration['clients']['simple_timeout']['connections'][0]['timeout']);
        $this->assertSame(234, $configuration['clients']['connect_timeout']['connections'][0]['connectTimeout']);
    }

    public function testAWSConfig()
    {
        $configuration = $this->getConfigs([
            'clients' => [
                'default' => [
                    'aws_access_key_id' => 'AWS_KEY',
                    'aws_secret_access_key' => 'AWS_SECRET',
                    'aws_region' => 'AWS_REGION',
                    'aws_session_token' => 'AWS_SESSION_TOKEN',
                    'ssl' => true,
                ],
            ],
        ]);

        $connection = $configuration['clients']['default']['connections'][0];
        $this->assertSame('AWS_KEY', $connection['aws_access_key_id']);
        $this->assertSame('AWS_SECRET', $connection['aws_secret_access_key']);
        $this->assertSame('AWS_REGION', $connection['aws_region']);
        $this->assertSame('AWS_SESSION_TOKEN', $connection['aws_session_token']);
        $this->assertTrue($connection['ssl']);
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
        $connection = $configuration['clients']['default']['connections'][0];
        $this->assertSame([400, 403, 404], $connection['http_error_codes']);

        // test custom
        $configuration = $this->getConfigs([
            'clients' => [
                'default' => [
                    'http_error_codes' => ['HTTP_ERROR_CODE'],
                ],
            ],
        ]);
        $connection = $configuration['clients']['default']['connections'][0];
        $this->assertSame(['HTTP_ERROR_CODE'], $connection['http_error_codes']);
    }

    public function testIndexTemplates()
    {
        $configuration = $this->getConfigs(
            [
                'index_templates' => [
                    'some_template' => [
                        'template' => 'some_template_*',
                        'client' => 'default',
                        'properties' => [
                            'some_field' => [],
                        ],
                    ],
                ],
            ]
        );
        $indexTemplate = $configuration['index_templates']['some_template'];
        $this->assertSame('some_template_*', $indexTemplate['template']);
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
