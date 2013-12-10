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

    public function testDynamicTemplateNodes()
    {
        $tree = $this->configuration->getConfigTree();
        $children = $tree->getChildren();
        $children = $children['indexes']->getPrototype()->getChildren();
        $typeNodes = $children['types']->getPrototype()->getChildren();
        $dynamicTemplates = $typeNodes['dynamic_templates']->getPrototype()->getChildren();

        $this->assertArrayHasKey('match', $dynamicTemplates);
        $this->assertInstanceOf('Symfony\Component\Config\Definition\ScalarNode', $dynamicTemplates['match']);
        $this->assertNull($dynamicTemplates['match']->getDefaultValue());

        $this->assertArrayHasKey('match_mapping_type', $dynamicTemplates);
        $this->assertInstanceOf('Symfony\Component\Config\Definition\ScalarNode', $dynamicTemplates['match_mapping_type']);
        $this->assertNull($dynamicTemplates['match_mapping_type']->getDefaultValue());

        $this->assertArrayHasKey('unmatch', $dynamicTemplates);
        $this->assertInstanceOf('Symfony\Component\Config\Definition\ScalarNode', $dynamicTemplates['unmatch']);
        $this->assertNull($dynamicTemplates['unmatch']->getDefaultValue());

        $this->assertArrayHasKey('path_match', $dynamicTemplates);
        $this->assertInstanceOf('Symfony\Component\Config\Definition\ScalarNode', $dynamicTemplates['path_match']);
        $this->assertNull($dynamicTemplates['path_match']->getDefaultValue());

        $this->assertArrayHasKey('path_unmatch', $dynamicTemplates);
        $this->assertInstanceOf('Symfony\Component\Config\Definition\ScalarNode', $dynamicTemplates['v']);
        $this->assertNull($dynamicTemplates['path_unmatch']->getDefaultValue());

        $this->assertArrayHasKey('match_pattern', $dynamicTemplates);
        $this->assertInstanceOf('Symfony\Component\Config\Definition\ScalarNode', $dynamicTemplates['match_pattern']);
        $this->assertNull($dynamicTemplates['match_pattern']->getDefaultValue());

        $this->assertArrayHasKey('mapping', $dynamicTemplates);
        $this->assertInstanceOf('Symfony\Component\Config\Definition\ArrayNode', $dynamicTemplates['mapping']);
    }

    public function testDynamicTemplateMappingNodes()
    {
        $tree = $this->configuration->getConfigTree();
        $children = $tree->getChildren();
        $children = $children['indexes']->getPrototype()->getChildren();
        $typeNodes = $children['types']->getPrototype()->getChildren();
        $dynamicTemplates = $typeNodes['dynamic_templates']->getPrototype()->getChildren();
        $mapping = $dynamicTemplates['mapping']->getChildren();

        $this->assertArrayHasKey('type', $mapping);
        $this->assertInstanceOf('Symfony\Component\Config\Definition\ScalarNode', $mapping['type']);
        $this->assertSame('string', $mapping['type']->getDefaultValue());

        $this->assertArrayHasKey('index', $mapping);
        $this->assertInstanceOf('Symfony\Component\Config\Definition\ScalarNode', $mapping['index']);
        $this->assertNull($mapping['index']->getDefaultValue());
    }
}
