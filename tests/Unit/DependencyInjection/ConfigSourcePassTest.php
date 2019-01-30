<?php

namespace FOS\ElasticaBundle\Tests\Unit\DependencyInjection;

use FOS\ElasticaBundle\DependencyInjection\Compiler\ConfigSourcePass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ConfigSourcePassTest extends TestCase
{
    /** @var ContainerBuilder */
    private $container;

    protected function setUp()
    {
        $this->container = $this->prophesize(ContainerBuilder::class);
    }

    public function testProcessWithoutConfigManager()
    {
        $this->container
            ->hasDefinition('fos_elastica.config_manager')
            ->shouldBeCalled()
            ->willReturn(false);

        $pass = new ConfigSourcePass();
        $pass->process($this->container->reveal());

        $this->container->getDefinition('fos_elastica.config_manager')->shouldNotBeCalled();
        $this->container->getDefinition('fos_elastica.config_manager.index_templates')->shouldNotBeCalled();
    }

    public function testProcessWithConfigManager()
    {
        $this->container
            ->hasDefinition('fos_elastica.config_manager')
            ->shouldBeCalled()
            ->willReturn(true);

        $this->container
            ->findTaggedServiceIds('fos_elastica.config_source')
            ->shouldBeCalled()
            ->willReturn(
                [
                    'index_definition_id' => null,
                    'index_template_definition_id' => null,
                ]
            );

        $indexDefinition = $this->prophesize(Definition::class);
        $indexDefinition->getTag('fos_elastica.config_source')
            ->shouldBeCalled()
            ->willReturn([]);
        $this->container
            ->findDefinition('index_definition_id')
            ->shouldBeCalled()
            ->willReturn($indexDefinition->reveal());


        $indexTemplateDefinition = $this->prophesize(Definition::class);
        $indexTemplateDefinition->getTag('fos_elastica.config_source')
            ->shouldBeCalled()
            ->willReturn([]);
        $this->container
            ->findDefinition('index_template_definition_id')
            ->shouldBeCalled()
            ->willReturn($indexTemplateDefinition->reveal());

        $configManagerDefinition = $this->prophesize(Definition::class);
        $configManagerDefinition->replaceArgument(0, ['index_definition_id']);
        $this->container
            ->getDefinition('fos_elastica.config_manager')
            ->shouldBeCalled()
            ->willReturn($configManagerDefinition);

        $templateConfigManagerDefinition = $this->prophesize(Definition::class);
        $templateConfigManagerDefinition->replaceArgument(0, ['index_template_definition_id']);
        $this->container
            ->getDefinition('fos_elastica.config_manager.index_templates')
            ->shouldBeCalled()
            ->willReturn($templateConfigManagerDefinition);

        $pass = new ConfigSourcePass();
        $pass->process($this->container->reveal());
    }
}
