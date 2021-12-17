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

use FOS\ElasticaBundle\Configuration\ConfigManager;
use FOS\ElasticaBundle\Configuration\Source\ContainerSource;
use FOS\ElasticaBundle\DependencyInjection\Compiler\ConfigSourcePass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @internal
 */
class ConfigSourcePassTest extends TestCase
{
    /** @var ContainerBuilder */
    private $container;

    protected function setUp(): void
    {
        $this->container = new ContainerBuilder();
    }

    public function testProcessWithoutConfigManager()
    {
        $configManagerDefinition = new Definition(ConfigManager::class);
        $configManagerDefinition->addArgument([]);
        $this->container->setDefinition('fos_elastica.config_manager', $configManagerDefinition);

        $configManagerIndexTemplatesDefinition = new Definition(ConfigManager::class);
        $configManagerIndexTemplatesDefinition->addArgument([]);
        $this->container->setDefinition('fos_elastica.config_manager.index_templates', $configManagerIndexTemplatesDefinition);

        $pass = new ConfigSourcePass();
        $pass->process($this->container);

        $this->assertSame([], $this->container->getDefinition('fos_elastica.config_manager')->getArgument(0));
        $this->assertSame([], $this->container->getDefinition('fos_elastica.config_manager.index_templates')->getArgument(0));
    }

    public function testProcessWithConfigManager()
    {
        $configManagerDefinition = new Definition(ConfigManager::class);
        $configManagerDefinition->addArgument([]);
        $this->container->setDefinition('fos_elastica.config_manager', $configManagerDefinition);

        $configManagerIndexTemplatesDefinition = new Definition(ConfigManager::class);
        $configManagerIndexTemplatesDefinition->addArgument([]);
        $this->container->setDefinition('fos_elastica.config_manager.index_templates', $configManagerIndexTemplatesDefinition);

        $indexDefinition = new Definition(ContainerSource::class);
        $indexDefinition->addTag('fos_elastica.config_source');

        $this->container->setDefinition('index_definition_id', $indexDefinition);

        $indexTemplateDefinition = new Definition(ContainerSource::class);
        $indexTemplateDefinition->addTag('fos_elastica.config_source');

        $this->container->setDefinition('index_template_definition_id', $indexTemplateDefinition);

        $pass = new ConfigSourcePass();
        $pass->process($this->container);

        $argument = $configManagerDefinition->getArgument(0);

        $this->assertIsArray($argument);
        $this->assertCount(2, $argument);
        $this->assertInstanceOf(Reference::class, $argument[0]);
        $this->assertSame('index_definition_id', $argument[0]->__toString());
        $this->assertInstanceOf(Reference::class, $argument[1]);
        $this->assertSame('index_template_definition_id', $argument[1]->__toString());
    }
}
