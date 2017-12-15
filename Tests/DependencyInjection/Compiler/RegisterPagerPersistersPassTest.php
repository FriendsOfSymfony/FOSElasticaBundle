<?php

namespace FOS\ElasticaBundle\Tests\DependencyInjection\Compiler;

use FOS\ElasticaBundle\DependencyInjection\Compiler\RegisterPagerPersistersPass;
use FOS\ElasticaBundle\Persister\PagerPersisterInterface;
use FOS\ElasticaBundle\Persister\PagerPersisterRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RegisterPagerPersistersPassTest extends TestCase
{
    public function testShouldImplementCompilerPassInterface()
    {
        $rc = new \ReflectionClass(RegisterPagerPersistersPass::class);

        $this->assertTrue($rc->implementsInterface(CompilerPassInterface::class));
    }

    public function testCouldBeConstructedWithoutAnyArguments()
    {
        new RegisterPagerPersistersPass();
    }

    public function testShouldDoNothingIfPersisterRegistryServiceIsMissing()
    {
        $container = new ContainerBuilder();
        $pass = new RegisterPagerPersistersPass();

        $container->setDefinition('foo_persister', $this->createPagerPersisterDefinition(['persisterName' => 'foo']));
        $container->setDefinition('bar_persister', $this->createPagerPersisterDefinition(['persisterName' => 'bar']));
        $container->setDefinition('baz_persister', $this->createPagerPersisterDefinition(['persisterName' => 'baz']));

        $pass->process($container);
    }

    public function testShouldRegisterTaggedPagerPersisters()
    {
        $container = new ContainerBuilder();
        $pass = new RegisterPagerPersistersPass();

        $registry = new Definition(PagerPersisterRegistry::class);
        $registry->addArgument([]);

        $container->setDefinition('fos_elastica.pager_persister_registry', $registry);

        $container->setDefinition('foo_persister', $this->createPagerPersisterDefinition(['persisterName' => 'foo']));
        $container->setDefinition('bar_persister', $this->createPagerPersisterDefinition(['persisterName' => 'bar']));

        $pass->process($container);

        $this->assertEquals([
            'foo' => 'foo_persister',
            'bar' => 'bar_persister',
        ], $registry->getArgument(0));
    }

    public function testThrowsIfTagMissesPersisterNameAttribute()
    {
        $container = new ContainerBuilder();
        $pass = new RegisterPagerPersistersPass();

        $container->setDefinition('fos_elastica.pager_persister_registry', new Definition());

        $container->setDefinition('a_persister', $this->createPagerPersisterDefinition([]));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Elastica pager persister "a_persister" must specify the "persisterName" attribute.');

        $pass->process($container);
    }

    public function testThrowsIfPersisterForSuchNameHasBeenAlreadyRegistered()
    {
        $container = new ContainerBuilder();
        $pass = new RegisterPagerPersistersPass();

        $container->setDefinition('fos_elastica.pager_persister_registry', new Definition());

        $container->setDefinition('a_foo_persister', $this->createPagerPersisterDefinition(['persisterName' => 'foo']));
        $container->setDefinition('a_bar_persister', $this->createPagerPersisterDefinition(['persisterName' => 'foo']));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot register pager persister "a_bar_persister". The pager persister "a_foo_persister" has been registered for same name "foo"');

        $pass->process($container);
    }

    public function testThrowsIfPagerPersisterServiceDoesNotImplementPagerPersisterInterface()
    {
        $container = new ContainerBuilder();
        $pass = new RegisterPagerPersistersPass();

        $container->setDefinition('fos_elastica.pager_persister_registry', new Definition());

        $persister = $this->createPagerPersisterDefinition(['persisterName' => 'foo']);
        $persister->setClass(\stdClass::class);

        $container->setDefinition('a_foo_persister', $persister);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Elastica pager persister "a_foo_persister" with class "stdClass" must implement "FOS\ElasticaBundle\Persister\PagerPersisterInterface".');

        $pass->process($container);
    }

    public function testShouldSkipClassCheckIfDefinitionHasFactory()
    {
        $container = new ContainerBuilder();
        $pass = new RegisterPagerPersistersPass();

        $registry = new Definition(PagerPersisterRegistry::class);
        $registry->addArgument([]);

        $container->setDefinition('fos_elastica.pager_persister_registry', $registry);

        $persister = $this->createPagerPersisterDefinition(['persisterName' => 'foo']);
        $persister->setClass(\stdClass::class);
        $persister->setFactory('a_factory_function');

        $container->setDefinition('a_foo_persister', $persister);

        $pass->process($container);

        $this->assertEquals(['foo' => 'a_foo_persister'], $registry->getArgument(0));
    }

    /**
     * @param array $attributes
     * 
     * @return Definition
     */
    private function createPagerPersisterDefinition(array $attributes = array())
    {
        $definition = new Definition(PagerPersisterInterface::class);
        $definition->addTag('fos_elastica.pager_persister', $attributes);

        return $definition;
    }
}
