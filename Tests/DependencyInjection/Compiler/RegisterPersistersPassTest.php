<?php

namespace FOS\ElasticaBundle\Tests\DependencyInjection\Compiler;

use FOS\ElasticaBundle\DependencyInjection\Compiler\RegisterPersistersPass;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Persister\PagerPersisterInterface;
use FOS\ElasticaBundle\Persister\PersisterRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RegisterPersistersPassTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldImplementCompilerPassInterface()
    {
        $rc = new \ReflectionClass(RegisterPersistersPass::class);

        $this->assertTrue($rc->implementsInterface(CompilerPassInterface::class));
    }

    public function testCouldBeConstructedWithoutAnyArguments()
    {
        new RegisterPersistersPass();
    }

    public function testShouldDoNothingIfPersisterRegistryServiceIsMissing()
    {
        $container = new ContainerBuilder();
        $pass = new RegisterPersistersPass();

        $container->setDefinition('foo_persister', $this->createPersisterDefinition(array('type' => 'a')));
        $container->setDefinition('bar_persister', $this->createPersisterDefinition(array('index' => 'foo', 'type' => 'b')));
        $container->setDefinition('baz_persister', $this->createPersisterDefinition(array('index' => 'bar', 'type' => 'a')));

        $pass->process($container);
    }

    public function testShouldRegisterTaggedPagerPersisters()
    {
        $container = new ContainerBuilder();
        $pass = new RegisterPersistersPass();

        $registry = new Definition(PersisterRegistry::class);
        $registry->addArgument([]);

        $container->setParameter('fos_elastica.default_index', 'foo');
        $container->setDefinition('fos_elastica.persister_registry', $registry);

        $container->setDefinition('foo_persister', $this->createPersisterDefinition(array('type' => 'a')));
        $container->setDefinition('bar_persister', $this->createPersisterDefinition(array('index' => 'foo', 'type' => 'b')));
        $container->setDefinition('baz_persister', $this->createPersisterDefinition(array('index' => 'bar', 'type' => 'a')));

        $pass->process($container);

        $this->assertEquals([
            'foo' => [
                'a' => 'foo_persister',
                'b' => 'bar_persister',
            ],
            'bar' => [
                'a' => 'baz_persister'
            ],
        ], $registry->getArgument(0));
    }

    public function testThrowsIfTagMissesTypeAttribute()
    {
        $container = new ContainerBuilder();
        $pass = new RegisterPersistersPass();

        $container->setDefinition('fos_elastica.persister_registry', new Definition());
        $container->setParameter('fos_elastica.default_index', 'foo');

        $container->setDefinition('a_persister', $this->createPersisterDefinition([]));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Elastica persister "a_persister" must specify the "type" attribute.');

        $pass->process($container);
    }

    public function testThrowsIfPersisterForSuchIndexTypeHasBeenAlreadyRegistered()
    {
        $container = new ContainerBuilder();
        $pass = new RegisterPersistersPass();

        $container->setDefinition('fos_elastica.persister_registry', new Definition());
        $container->setParameter('fos_elastica.default_index', 'foo');

        $container->setDefinition('a_foo_persister', $this->createPersisterDefinition(['index' => 'foo', 'type' => 'bar']));
        $container->setDefinition('a_bar_persister', $this->createPersisterDefinition(['index' => 'foo', 'type' => 'bar']));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot register persister "a_bar_persister". The persister "a_foo_persister" has been registered for same index "foo" and type "bar"');

        $pass->process($container);
    }

    public function testThrowsIfPersisterServiceDoesNotImplementPagerPersisterInterface()
    {
        $container = new ContainerBuilder();
        $pass = new RegisterPersistersPass();

        $container->setDefinition('fos_elastica.persister_registry', new Definition());
        $container->setParameter('fos_elastica.default_index', 'foo');

        $persister = $this->createPersisterDefinition(['index' => 'foo', 'type' => 'bar']);
        $persister->setClass(\stdClass::class);

        $container->setDefinition('a_foo_persister', $persister);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Elastica persister "a_foo_persister" with class "stdClass" must implement "FOS\ElasticaBundle\Persister\ObjectPersisterInterface".');

        $pass->process($container);
    }

    public function testShouldSkipClassCheckIfDefinitionHasFactory()
    {
        $container = new ContainerBuilder();
        $pass = new RegisterPersistersPass();

        $registry = new Definition(PersisterRegistry::class);
        $registry->addArgument([]);

        $container->setParameter('fos_elastica.default_index', 'foo');
        $container->setDefinition('fos_elastica.persister_registry', $registry);

        $persister = $this->createPersisterDefinition(['index' => 'foo', 'type' => 'bar']);
        $persister->setClass(\stdClass::class);
        $persister->setFactory('a_factory_function');

        $container->setDefinition('a_foo_persister', $persister);

        $pass->process($container);

        $this->assertEquals(['foo' => ['bar' => 'a_foo_persister']], $registry->getArgument(0));
    }

    /**
     * @param array $attributes
     * 
     * @return Definition
     */
    private function createPersisterDefinition(array $attributes = array())
    {
        $definition = new Definition(ObjectPersisterInterface::class);
        $definition->addTag('fos_elastica.persister', $attributes);

        return $definition;
    }
}
