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

        return $this->processor->processConfiguration($configuration, array($configArray));
    }

    public function testUnconfiguredConfiguration()
    {
        $configuration = $this->getConfigs(array());

        $this->assertSame(array(
            'clients' => array(),
            'indexes' => array(),
            'default_manager' => 'orm',
        ), $configuration);
    }

    public function testClientConfiguration()
    {
        $configuration = $this->getConfigs(array(
            'clients' => array(
                'default' => array(
                    'url' => 'http://localhost:9200',
                    'retryOnConflict' => 5,
                ),
                'clustered' => array(
                    'connections' => array(
                        array(
                            'url' => 'http://es1:9200',
                            'headers' => array(
                                'Authorization: Basic QWxhZGRpbjpvcGVuIHNlc2FtZQ==',
                            ),
                        ),
                        array(
                            'url' => 'http://es2:9200',
                            'headers' => array(
                                'Authorization: Basic QWxhZGRpbjpvcGVuIHNlc2FtZQ==',
                            ),
                        ),
                    ),
                ),
            ),
        ));

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
        $configuration = $this->getConfigs(array(
            'clients' => array(
                'logging_enabled' => array(
                    'url' => 'http://localhost:9200',
                    'logger' => true,
                ),
                'logging_disabled' => array(
                    'url' => 'http://localhost:9200',
                    'logger' => false,
                ),
                'logging_not_mentioned' => array(
                    'url' => 'http://localhost:9200',
                ),
                'logging_custom' => array(
                    'url' => 'http://localhost:9200',
                    'logger' => 'custom.service',
                ),
            ),
        ));

        $this->assertCount(4, $configuration['clients']);

        $this->assertSame('fos_elastica.logger', $configuration['clients']['logging_enabled']['connections'][0]['logger']);
        $this->assertFalse($configuration['clients']['logging_disabled']['connections'][0]['logger']);
        $this->assertSame('fos_elastica.logger', $configuration['clients']['logging_not_mentioned']['connections'][0]['logger']);
        $this->assertSame('custom.service', $configuration['clients']['logging_custom']['connections'][0]['logger']);
    }

    public function testSlashIsAddedAtTheEndOfServerUrl()
    {
        $config = array(
            'clients' => array(
                'default' => array('url' => 'http://www.github.com'),
            ),
        );
        $configuration = $this->getConfigs($config);

        $this->assertSame('http://www.github.com/', $configuration['clients']['default']['connections'][0]['url']);
    }

    public function testTypeConfig()
    {
        $this->getConfigs(array(
            'clients' => array(
                'default' => array('url' => 'http://localhost:9200'),
            ),
            'indexes' => array(
                'test' => array(
                    'type_prototype' => array(
                        'analyzer' => 'custom_analyzer',
                        'persistence' => array(
                            'identifier' => 'ID',
                        ),
                        'serializer' => array(
                            'groups' => array('Search'),
                            'version' => 1,
                            'serialize_null' => false,
                        ),
                    ),
                    'types' => array(
                        'test' => array(
                            'properties' => array(
                                'title' => array(),
                                'published' => array('type' => 'datetime'),
                                'body' => null,
                            ),
                            'persistence' => array(
                                'listener' => array(
                                    'logger' => true,
                                ),
                            ),
                        ),
                        'test2' => array(
                            'properties' => array(
                                'title' => null,
                                'children' => array(
                                    'type' => 'nested',
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ));
    }

    public function testClientConfigurationNoUrl()
    {
        $configuration = $this->getConfigs(array(
            'clients' => array(
                'default' => array(
                    'host' => 'localhost',
                    'port' => 9200,
                ),
            ),
        ));

        $this->assertTrue(empty($configuration['clients']['default']['connections'][0]['url']));
    }

    public function testUnconfiguredType()
    {
        $configuration = $this->getConfigs(array(
                'clients' => array(
                    'default' => array('url' => 'http://localhost:9200'),
                ),
                'indexes' => array(
                    'test' => array(
                        'types' => array(
                            'test' => null,
                        ),
                    ),
                ),
            ));

        $this->assertArrayHasKey('properties', $configuration['indexes']['test']['types']['test']);
    }

    public function testNestedProperties()
    {
        $this->getConfigs(array(
            'clients' => array(
                'default' => array('url' => 'http://localhost:9200'),
            ),
            'indexes' => array(
                'test' => array(
                    'types' => array(
                        'user' => array(
                            'properties' => array(
                                'field1' => array(),
                            ),
                            'persistence' => array(),
                        ),
                        'user_profile' => array(
                            '_parent' => array(
                                'type' => 'user',
                            ),
                            'properties' => array(
                                'field1' => array(),
                                'field2' => array(
                                    'type' => 'nested',
                                    'properties' => array(
                                        'nested_field1' => array(
                                            'type' => 'integer',
                                        ),
                                        'nested_field2' => array(
                                            'type' => 'object',
                                            'properties' => array(
                                                'id' => array(
                                                    'type' => 'integer',
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ));
    }

    public function testCompressionConfig()
    {
        $configuration = $this->getConfigs(array(
            'clients' => array(
                'compression_enabled' => array(
                    'compression' => true,
                ),
                'compression_disabled' => array(
                    'compression' => false,
                ),
            ),
        ));

        $this->assertTrue($configuration['clients']['compression_enabled']['connections'][0]['compression']);
        $this->assertFalse($configuration['clients']['compression_disabled']['connections'][0]['compression']);
    }

    public function testCompressionDefaultConfig()
    {
        $configuration = $this->getConfigs(array(
            'clients' => array(
                'default' => array(),
            ),
        ));

        $this->assertFalse($configuration['clients']['default']['connections'][0]['compression']);
    }

    public function testTimeoutConfig()
    {
        $configuration = $this->getConfigs(array(
            'clients' => array(
                'simple_timeout' => array(
                    'url' => 'http://localhost:9200',
                    'timeout' => 123,
                ),
                'connect_timeout' => array(
                    'url' => 'http://localhost:9200',
                    'connectTimeout' => 234,
                ),
            ),
        ));

        $this->assertSame(123, $configuration['clients']['simple_timeout']['connections'][0]['timeout']);
        $this->assertSame(234, $configuration['clients']['connect_timeout']['connections'][0]['connectTimeout']);
    }

    public function testAWSConfig()
    {
        $configuration = $this->getConfigs(array(
            'clients' => array(
                'default' => array(
                    'aws_access_key_id' => 'AWS_KEY',
                    'aws_secret_access_key' => 'AWS_SECRET',
                    'aws_region' => 'AWS_REGION',
                    'aws_session_token' => 'AWS_SESSION_TOKEN',
                ),
            ),
        ));

        $connection = $configuration['clients']['default']['connections'][0];
        $this->assertSame('AWS_KEY', $connection['aws_access_key_id']);
        $this->assertSame('AWS_SECRET', $connection['aws_secret_access_key']);
        $this->assertSame('AWS_REGION', $connection['aws_region']);
        $this->assertSame('AWS_SESSION_TOKEN', $connection['aws_session_token']);
    }
}
