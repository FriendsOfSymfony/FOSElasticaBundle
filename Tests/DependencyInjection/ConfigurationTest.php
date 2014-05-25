<?php

namespace FOS\ElasticaBundle\Tests\Resetter\DependencyInjection;

use FOS\ElasticaBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

/**
 * ConfigurationTest
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
            'default_manager' => 'orm'
        ), $configuration);
    }

    public function testClientConfiguration()
    {
        $configuration = $this->getConfigs(array(
            'clients' => array(
                'default' => array(
                    'url' => 'http://localhost:9200',
                ),
                'clustered' => array(
                    'servers' => array(
                        array(
                            'url' => 'http://es1:9200',
                            'headers' => array(
                                'Authorization: Basic QWxhZGRpbjpvcGVuIHNlc2FtZQ=='
                            )
                        ),
                        array(
                            'url' => 'http://es2:9200',
                            'headers' => array(
                                'Authorization: Basic QWxhZGRpbjpvcGVuIHNlc2FtZQ=='
                            )
                        ),
                    )
                )
            )
        ));

        $this->assertCount(2, $configuration['clients']);
        $this->assertCount(1, $configuration['clients']['default']['servers']);
        $this->assertCount(0, $configuration['clients']['default']['servers'][0]['headers']);

        $this->assertCount(2, $configuration['clients']['clustered']['servers']);
        $this->assertEquals('http://es2:9200/', $configuration['clients']['clustered']['servers'][1]['url']);
        $this->assertCount(1, $configuration['clients']['clustered']['servers'][1]['headers']);
        $this->assertEquals('Authorization: Basic QWxhZGRpbjpvcGVuIHNlc2FtZQ==', $configuration['clients']['clustered']['servers'][0]['headers'][0]);
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
                    'logger' => 'custom.service'
                ),
            )
        ));

        $this->assertCount(4, $configuration['clients']);

        $this->assertEquals('fos_elastica.logger', $configuration['clients']['logging_enabled']['servers'][0]['logger']);
        $this->assertFalse($configuration['clients']['logging_disabled']['servers'][0]['logger']);
        $this->assertEquals('fos_elastica.logger', $configuration['clients']['logging_not_mentioned']['servers'][0]['logger']);
        $this->assertEquals('custom.service', $configuration['clients']['logging_custom']['servers'][0]['logger']);
    }

    public function testSlashIsAddedAtTheEndOfServerUrl()
    {
        $config = array(
            'clients' => array(
                'default' => array('url' => 'http://www.github.com'),
            ),
        );
        $configuration = $this->getConfigs($config);

        $this->assertEquals('http://www.github.com/', $configuration['clients']['default']['servers'][0]['url']);
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
                        'index_analyzer' => 'custom_analyzer',
                        'persistence' => array(
                            'identifier' => 'ID',
                        ),
                        'serializer' => array(
                            'groups' => array('Search'),
                            'version' => 1
                        )
                    ),
                    'types' => array(
                        'test' => array(
                            'mappings' => array(
                                'title' => array(),
                                'published' => array('type' => 'datetime'),
                                'body' => null,
                            ),
                            'persistence' => array(
                                'listener' => array(
                                    'logger' => true,
                                )
                            )
                        ),
                        'test2' => array(
                            'mappings' => array(
                                'title' => null,
                                'children' => array(
                                    'type' => 'nested',
                                )
                            )
                        )
                    )
                )
            )
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
            )
        ));

        $this->assertTrue(empty($configuration['clients']['default']['servers'][0]['url']));
    }
}
