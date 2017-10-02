<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Resetter\DependencyInjection;

use FOS\ElasticaBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

/**
 * ConfigurationTest.
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Processor
     */
    private $processor;

    public function setUp()
    {
        $this->processor = new Processor();
    }

    private function getConfigs(array $configArray)
    {
        $configuration = new Configuration(true);

        return $this->processor->processConfiguration($configuration, [$configArray]);
    }

    public function testUnconfiguredConfiguration()
    {
        $configuration = $this->getConfigs([]);

        $this->assertSame([
            'clients' => [],
            'indexes' => [],
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

    public function testTypeConfig()
    {
        $this->getConfigs([
            'clients' => [
                'default' => ['url' => 'http://localhost:9200'],
            ],
            'indexes' => [
                'test' => [
                    'type_prototype' => [
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
                    'types' => [
                        'test' => [
                            'properties' => [
                                'title' => [],
                                'published' => ['type' => 'datetime'],
                                'body' => null,
                            ],
                            'persistence' => [
                                'listener' => [
                                    'logger' => true,
                                ],
                            ],
                        ],
                        'test2' => [
                            'properties' => [
                                'title' => null,
                                'children' => [
                                    'type' => 'nested',
                                ],
                            ],
                        ],
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
                        'types' => [
                            'test' => null,
                        ],
                    ],
                ],
            ]);

        $this->assertArrayHasKey('properties', $configuration['indexes']['test']['types']['test']);
    }

    public function testNestedProperties()
    {
        $this->getConfigs([
            'clients' => [
                'default' => ['url' => 'http://localhost:9200'],
            ],
            'indexes' => [
                'test' => [
                    'types' => [
                        'user' => [
                            'properties' => [
                                'field1' => [],
                            ],
                            'persistence' => [],
                        ],
                        'user_profile' => [
                            '_parent' => [
                                'type' => 'user',
                            ],
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
                    'ssl' => true
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
}
