<?php
/**
 * ConfigurationTest.php
 *
 * User: mikey
 * Date: 25/10/2013
 * Time: 10:39
 */

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
    private $testClass;

    public function setUp()
    {
        $this->testClass = new Configuration(array());
    }

    public function testBasicSanity()
    {
        $tree = $this->testClass->getConfigTree();
        $this->assertInstanceOf('Symfony\Component\Config\Definition\ArrayNode', $tree);
    }

    public function testEmptyConfigContainsMappingOptionsNode()
    {
        $tree = $this->testClass->getConfigTree();
        $children = $tree->getChildren();
        $indexes  = $children['indexes'];
        $children = $indexes->getPrototype()->getChildren();
        $types   = $children['types']->getPrototype();
        $typeNodes = $types->getChildren();
        $this->assertArrayHasKey('mapping_options', $typeNodes);
        $this->assertInstanceOf('Symfony\Component\Config\Definition\VariableNode', $typeNodes['mapping_options']);
        $this->assertEmpty($typeNodes['mapping_options']->getDefaultValue());
    }
}
