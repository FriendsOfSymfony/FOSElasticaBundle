<?php

namespace FOS\ElasticaBundle\Tests\Resetter\DependencyInjection;

use FOS\ElasticaBundle\DependencyInjection\Configuration;

/**
 * ConfigurationTest
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Configuration
     */
    private $configuration;

    public function setUp()
    {
        $this->configuration = new Configuration(array());
    }

    public function testEmptyConfigContainsFormatMappingOptionNode()
    {
        $tree = $this->configuration->getConfigTree();
        $children = $tree->getChildren();
        $children = $children['indexes']->getPrototype()->getChildren();
        $typeNodes = $children['types']->getPrototype()->getChildren();
        $mappings = $typeNodes['mappings']->getPrototype()->getChildren();

        $this->assertArrayHasKey('format', $mappings);
        $this->assertInstanceOf('Symfony\Component\Config\Definition\ScalarNode', $mappings['format']);
        $this->assertNull($mappings['format']->getDefaultValue());
    }
}
