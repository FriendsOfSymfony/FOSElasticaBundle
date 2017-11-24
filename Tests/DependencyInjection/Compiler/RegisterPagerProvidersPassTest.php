<?php

namespace FOS\ElasticaBundle\Tests\DependencyInjection\Compiler;

use FOS\ElasticaBundle\DependencyInjection\Compiler\RegisterPagerProvidersPass;
use FOS\ElasticaBundle\DependencyInjection\Compiler\RegisterProvidersPass;
use FOS\ElasticaBundle\Provider\PagerProviderInterface;
use FOS\ElasticaBundle\Provider\PagerProviderRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RegisterPagerProvidersPassTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldImplementCompilerPassInterface()
    {
        $rc = new \ReflectionClass(RegisterPagerProvidersPass::class);

        $this->assertTrue($rc->implementsInterface(CompilerPassInterface::class));
    }

    public function testCouldBeConstructedWithoutAnyArguments()
    {
        new RegisterPagerProvidersPass();
    }

    public function testShouldDoNothingIfPagerProviderRegistryServiceIsMissing()
    {
        $container = new ContainerBuilder();
        $pass = new RegisterPagerProvidersPass();

        $container->setDefinition('foo_provider', $this->createProviderDefinition(array('type' => 'a')));
        $container->setDefinition('bar_provider', $this->createProviderDefinition(array('index' => 'foo', 'type' => 'b')));
        $container->setDefinition('baz_provider', $this->createProviderDefinition(array('index' => 'bar', 'type' => 'a')));

        $pass->process($container);
    }

    public function testShouldRegisterTaggedPagerProviders()
    {
        $container = new ContainerBuilder();
        $pass = new RegisterPagerProvidersPass();

        $registry = new Definition(PagerProviderRegistry::class);
        $registry->addArgument([]);

        $container->setParameter('fos_elastica.default_index', 'foo');
        $container->setDefinition('fos_elastica.pager_provider_registry', $registry);

        $container->setDefinition('foo_provider', $this->createProviderDefinition(array('type' => 'a')));
        $container->setDefinition('bar_provider', $this->createProviderDefinition(array('index' => 'foo', 'type' => 'b')));
        $container->setDefinition('baz_provider', $this->createProviderDefinition(array('index' => 'bar', 'type' => 'a')));

        $pass->process($container);

        $this->assertEquals([
            'foo' => [
                'a' => 'foo_provider',
                'b' => 'bar_provider',
            ],
            'bar' => [
                'a' => 'baz_provider'
            ],
        ], $registry->getArgument(0));
    }

    public function testThrowsIfTagMissesTypeAttribute()
    {
        $container = new ContainerBuilder();
        $pass = new RegisterPagerProvidersPass();

        $container->setDefinition('fos_elastica.pager_provider_registry', new Definition());
        $container->setParameter('fos_elastica.default_index', 'foo');

        $container->setDefinition('a_provider', $this->createProviderDefinition([]));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Elastica provider "a_provider" must specify the "type" attribute.');

        $pass->process($container);
    }

    public function testThrowsIfProviderForSuchIndexTypeHasBeenAlreadyRegistered()
    {
        $container = new ContainerBuilder();
        $pass = new RegisterPagerProvidersPass();

        $container->setDefinition('fos_elastica.pager_provider_registry', new Definition());
        $container->setParameter('fos_elastica.default_index', 'foo');

        $container->setDefinition('a_foo_provider', $this->createProviderDefinition(['index' => 'foo', 'type' => 'bar']));
        $container->setDefinition('a_bar_provider', $this->createProviderDefinition(['index' => 'foo', 'type' => 'bar']));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot register provider "a_bar_provider". The provider "a_foo_provider" has been registered for same index "foo" and type "bar"');

        $pass->process($container);
    }

    public function testThrowsIfProviderServiceDoesNotImplementPagerProviderInterface()
    {
        $container = new ContainerBuilder();
        $pass = new RegisterPagerProvidersPass();

        $container->setDefinition('fos_elastica.pager_provider_registry', new Definition());
        $container->setParameter('fos_elastica.default_index', 'foo');

        $provider = $this->createProviderDefinition(['index' => 'foo', 'type' => 'bar']);
        $provider->setClass(\stdClass::class);

        $container->setDefinition('a_foo_provider', $provider);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Elastica provider "a_foo_provider" with class "stdClass" must implement ProviderInterface.');

        $pass->process($container);
    }

    public function testShouldSkipClassCheckIfDefinitionHasFactory()
    {
        $container = new ContainerBuilder();
        $pass = new RegisterPagerProvidersPass();

        $registry = new Definition(PagerProviderRegistry::class);
        $registry->addArgument([]);

        $container->setParameter('fos_elastica.default_index', 'foo');
        $container->setDefinition('fos_elastica.pager_provider_registry', $registry);

        $provider = $this->createProviderDefinition(['index' => 'foo', 'type' => 'bar']);
        $provider->setClass(\stdClass::class);
        $provider->setFactory('a_factory_function');

        $container->setDefinition('a_foo_provider', $provider);

        $pass->process($container);

        $this->assertEquals(['foo' => ['bar' => 'a_foo_provider']], $registry->getArgument(0));
    }

    /**
     * @param array $attributes
     * 
     * @return Definition
     */
    private function createProviderDefinition(array $attributes = array())
    {
        $definition = new Definition(PagerProviderInterface::class);
        $definition->addTag('fos_elastica.pager_provider', $attributes);

        return $definition;
    }
}
