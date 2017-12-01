<?php

namespace FOS\ElasticaBundle\Tests\DependencyInjection;

use Doctrine\ODM\MongoDB\DocumentManager;
use FOS\ElasticaBundle\DependencyInjection\FOSElasticaExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\Yaml\Yaml;

class FOSElasticaExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldAddParentParamToObjectPersisterCall()
    {
        $config = Yaml::parse(file_get_contents(__DIR__.'/fixtures/config.yml'));

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.debug', true);

        $extension = new FOSElasticaExtension();

        $extension->load($config, $containerBuilder);

        $this->assertTrue($containerBuilder->hasDefinition('fos_elastica.object_persister.test_index.child_field'));

        $persisterCallDefinition = $containerBuilder->getDefinition('fos_elastica.object_persister.test_index.child_field');

        $arguments = $persisterCallDefinition->getArguments();
        $arguments = $arguments['index_3'];

        $this->assertArrayHasKey('_parent', $arguments);
        $this->assertEquals('parent_field', $arguments['_parent']['type']);
    }

    public function testExtensionSupportsDriverlessTypePersistence()
    {
        $config = Yaml::parse(file_get_contents(__DIR__.'/fixtures/driverless_type.yml'));

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.debug', true);

        $extension = new FOSElasticaExtension();
        $extension->load($config, $containerBuilder);

        $this->assertTrue($containerBuilder->hasDefinition('fos_elastica.index.test_index'));
        $this->assertTrue($containerBuilder->hasDefinition('fos_elastica.index.test_index.driverless'));
        $this->assertFalse($containerBuilder->hasDefinition('fos_elastica.elastica_to_model_transformer.test_index.driverless'));
        $this->assertFalse($containerBuilder->hasDefinition('fos_elastica.object_persister.test_index.driverless'));
    }

    public function testShouldNotRegisterPagerProviderIfNotEnabled()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', true);

        $extension = new FOSElasticaExtension();
        $extension->load([
            'fos_elastica' => [
                'clients' => [
                    'default' => ['host' => 'a_host', 'port' => 'a_port'],
                ],
                'indexes' => [
                    'acme_index' => [
                        'types' => [
                            'acme_type' => [
                                'properties' => ['text' => null],
                                'persistence' => [
                                    'driver' => 'orm',
                                    'model' => 'AppBundle\Entity\Blog',
                                    'provider' => ['pager_provider' => false],
                                    'listener' => null,
                                    'finder' => null,
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ], $container);

        $this->assertFalse($container->hasDefinition('fos_elastica.pager_provider.acme_index.acme_type'));
    }

    public function testShouldRegisterDoctrineORMPagerProviderIfEnabled()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', true);

        $extension = new FOSElasticaExtension();
        $extension->load([
            'fos_elastica' => [
                'clients' => [
                    'default' => ['host' => 'a_host', 'port' => 'a_port'],
                ],
                'indexes' => [
                    'acme_index' => [
                        'types' => [
                            'acme_type' => [
                                'properties' => ['text' => null],
                                'persistence' => [
                                    'driver' => 'orm',
                                    'model' => 'theModelClass',
                                    'provider' => ['pager_provider' => true],
                                    'listener' => null,
                                    'finder' => null,
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ], $container);

        $this->assertTrue($container->hasDefinition('fos_elastica.pager_provider.acme_index.acme_type'));

        $definition = $container->getDefinition('fos_elastica.pager_provider.acme_index.acme_type');
        $this->assertInstanceOf(DefinitionDecorator::class, $definition);
        $this->assertSame('fos_elastica.pager_provider.prototype.orm', $definition->getParent());
        $this->assertSame('theModelClass', $definition->getArgument(1));
        $this->assertSame([
            'pager_provider' => true,
            'batch_size' => 100,
            'clear_object_manager' => true,
            'debug_logging' => true,
            'query_builder_method' => 'createQueryBuilder',
        ], $definition->getArgument(2));

        $this->assertSame([
            'fos_elastica.pager_provider' => [
                ['index' => 'acme_index', 'type' => 'acme_type'],
            ]
        ], $definition->getTags());
    }

    public function testShouldRegisterDoctrineMongoDBPagerProviderIfEnabled()
    {
        if (!class_exists(DocumentManager::class)) {
            $this->markTestSkipped('Doctrine MongoDB ODM is not available.');
        }

        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', true);

        $extension = new FOSElasticaExtension();
        $extension->load([
            'fos_elastica' => [
                'clients' => [
                    'default' => ['host' => 'a_host', 'port' => 'a_port'],
                ],
                'indexes' => [
                    'acme_index' => [
                        'types' => [
                            'acme_type' => [
                                'properties' => ['text' => null],
                                'persistence' => [
                                    'driver' => 'mongodb',
                                    'model' => 'theModelClass',
                                    'provider' => ['pager_provider' => true],
                                    'listener' => null,
                                    'finder' => null,
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ], $container);

        $this->assertTrue($container->hasDefinition('fos_elastica.pager_provider.acme_index.acme_type'));

        $definition = $container->getDefinition('fos_elastica.pager_provider.acme_index.acme_type');
        $this->assertInstanceOf(DefinitionDecorator::class, $definition);
        $this->assertSame('fos_elastica.pager_provider.prototype.mongodb', $definition->getParent());
        $this->assertSame('theModelClass', $definition->getArgument(1));
        $this->assertSame([
            'pager_provider' => true,
            'batch_size' => 100,
            'clear_object_manager' => true,
            'debug_logging' => true,
            'query_builder_method' => 'createQueryBuilder',
        ], $definition->getArgument(2));

        $this->assertSame([
            'fos_elastica.pager_provider' => [
                ['index' => 'acme_index', 'type' => 'acme_type'],
            ]
        ], $definition->getTags());
    }

    public function testShouldRegisterDoctrinePHPCRPagerProviderIfEnabled()
    {
        if (!class_exists(\Doctrine\ODM\PHPCR\DocumentManager::class)) {
            $this->markTestSkipped('Doctrine PHPCR is not present');
        }

        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', true);

        $extension = new FOSElasticaExtension();
        $extension->load([
            'fos_elastica' => [
                'clients' => [
                    'default' => ['host' => 'a_host', 'port' => 'a_port'],
                ],
                'indexes' => [
                    'acme_index' => [
                        'types' => [
                            'acme_type' => [
                                'properties' => ['text' => null],
                                'persistence' => [
                                    'driver' => 'phpcr',
                                    'model' => 'theModelClass',
                                    'provider' => ['pager_provider' => true],
                                    'listener' => null,
                                    'finder' => null,
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ], $container);

        $this->assertTrue($container->hasDefinition('fos_elastica.pager_provider.acme_index.acme_type'));

        $definition = $container->getDefinition('fos_elastica.pager_provider.acme_index.acme_type');
        $this->assertInstanceOf(DefinitionDecorator::class, $definition);
        $this->assertSame('fos_elastica.pager_provider.prototype.phpcr', $definition->getParent());
        $this->assertSame('theModelClass', $definition->getArgument(1));
        $this->assertSame([
            'pager_provider' => true,
            'batch_size' => 100,
            'clear_object_manager' => true,
            'debug_logging' => true,
            'query_builder_method' => 'createQueryBuilder',
        ], $definition->getArgument(2));

        $this->assertSame([
            'fos_elastica.pager_provider' => [
                ['index' => 'acme_index', 'type' => 'acme_type'],
            ]
        ], $definition->getTags());
    }

    public function testShouldRegisterPropel1PagerProviderIfEnabled()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', true);

        $extension = new FOSElasticaExtension();
        $extension->load([
            'fos_elastica' => [
                'clients' => [
                    'default' => ['host' => 'a_host', 'port' => 'a_port'],
                ],
                'indexes' => [
                    'acme_index' => [
                        'types' => [
                            'acme_type' => [
                                'properties' => ['text' => null],
                                'persistence' => [
                                    'driver' => 'propel',
                                    'model' => 'theModelClass',
                                    'provider' => ['pager_provider' => true],
                                    'listener' => null,
                                    'finder' => null,
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ], $container);

        $this->assertTrue($container->hasDefinition('fos_elastica.pager_provider.acme_index.acme_type'));

        $definition = $container->getDefinition('fos_elastica.pager_provider.acme_index.acme_type');
        $this->assertInstanceOf(DefinitionDecorator::class, $definition);
        $this->assertSame('fos_elastica.pager_provider.prototype.propel', $definition->getParent());
        $this->assertSame('theModelClass', $definition->getArgument(0));
        $this->assertSame([
            'pager_provider' => true,
            'batch_size' => 100,
            'clear_object_manager' => true,
            'debug_logging' => true,
            'query_builder_method' => 'createQueryBuilder',
        ], $definition->getArgument(1));

        $this->assertSame([
            'fos_elastica.pager_provider' => [
                ['index' => 'acme_index', 'type' => 'acme_type'],
            ]
        ], $definition->getTags());
    }
}
