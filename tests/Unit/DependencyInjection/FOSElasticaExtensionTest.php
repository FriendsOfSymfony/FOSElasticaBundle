<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\DependencyInjection;

use FOS\ElasticaBundle\DependencyInjection\FOSElasticaExtension;
use FOS\ElasticaBundle\Doctrine\RegisterListenersService;
use FOS\ElasticaBundle\Doctrine\MongoDBPagerProvider;
use FOS\ElasticaBundle\Doctrine\ORMPagerProvider;
use FOS\ElasticaBundle\Doctrine\PHPCRPagerProvider;
use FOS\ElasticaBundle\Persister\InPlacePagerPersister;
use FOS\ElasticaBundle\Persister\Listener\FilterObjectsListener;
use FOS\ElasticaBundle\Persister\PagerPersisterRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Yaml\Yaml;

class FOSElasticaExtensionTest extends TestCase
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
        $this->assertSame('parent_field', $arguments['_parent']['type']);
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
                                    'provider' => null,
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
        $this->assertInstanceOf(ChildDefinition::class, $definition);
        $this->assertSame('fos_elastica.pager_provider.prototype.orm', $definition->getParent());
        $this->assertSame('theModelClass', $definition->getArgument(2));
        $this->assertSame([
            'batch_size' => 100,
            'clear_object_manager' => true,
            'debug_logging' => true,
            'query_builder_method' => 'createQueryBuilder',
        ], $definition->getArgument(3));

        $this->assertSame([
            'fos_elastica.pager_provider' => [
                ['index' => 'acme_index', 'type' => 'acme_type'],
            ]
        ], $definition->getTags());

        $this->assertTrue($container->hasDefinition('fos_elastica.pager_provider.prototype.orm'));
        $this->assertSame(
            ORMPagerProvider::class,
            $container->getDefinition('fos_elastica.pager_provider.prototype.orm')->getClass()
        );
    }

    public function testShouldRegisterDoctrineMongoDBPagerProviderIfEnabled()
    {
        if (!class_exists(\Doctrine\ODM\MongoDB\DocumentManager::class)) {
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
                                    'provider' => null,
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
        $this->assertInstanceOf(ChildDefinition::class, $definition);
        $this->assertSame('fos_elastica.pager_provider.prototype.mongodb', $definition->getParent());
        $this->assertSame('theModelClass', $definition->getArgument(2));
        $this->assertSame([
            'batch_size' => 100,
            'clear_object_manager' => true,
            'debug_logging' => true,
            'query_builder_method' => 'createQueryBuilder',
        ], $definition->getArgument(3));

        $this->assertSame([
            'fos_elastica.pager_provider' => [
                ['index' => 'acme_index', 'type' => 'acme_type'],
            ]
        ], $definition->getTags());

        $this->assertTrue($container->hasDefinition('fos_elastica.pager_provider.prototype.mongodb'));
        $this->assertSame(
            MongoDBPagerProvider::class,
            $container->getDefinition('fos_elastica.pager_provider.prototype.mongodb')->getClass()
        );
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
                                    'provider' => null,
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
        $this->assertInstanceOf(ChildDefinition::class, $definition);
        $this->assertSame('fos_elastica.pager_provider.prototype.phpcr', $definition->getParent());
        $this->assertSame('theModelClass', $definition->getArgument(2));
        $this->assertSame([
            'batch_size' => 100,
            'clear_object_manager' => true,
            'debug_logging' => true,
            'query_builder_method' => 'createQueryBuilder',
        ], $definition->getArgument(3));

        $this->assertSame([
            'fos_elastica.pager_provider' => [
                ['index' => 'acme_index', 'type' => 'acme_type'],
            ]
        ], $definition->getTags());

        $this->assertTrue($container->hasDefinition('fos_elastica.pager_provider.prototype.phpcr'));
        $this->assertSame(
            PHPCRPagerProvider::class,
            $container->getDefinition('fos_elastica.pager_provider.prototype.phpcr')->getClass()
        );
    }

    public function testShouldRegisterInPlacePagerPersister()
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
                                    'provider' => null,
                                    'listener' => null,
                                    'finder' => null,
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ], $container);

        $this->assertTrue($container->hasDefinition('fos_elastica.in_place_pager_persister'));

        $definition = $container->getDefinition('fos_elastica.in_place_pager_persister');
        $this->assertSame(InPlacePagerPersister::class, $definition->getClass());

        $this->assertInstanceOf(Reference::class, $definition->getArgument(0));
        $this->assertSame('fos_elastica.persister_registry', (string) $definition->getArgument(0));

        $this->assertInstanceOf(Reference::class, $definition->getArgument(1));
        $this->assertSame('event_dispatcher', (string) $definition->getArgument(1));

        $this->assertSame([
            'fos_elastica.pager_persister' => [['persisterName' => 'in_place']]
        ], $definition->getTags());
    }

    public function testShouldRegisterRegisterListenersServiceForDoctrineProvider()
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
                                    'provider' => null,
                                    'listener' => null,
                                    'finder' => null,
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ], $container);

        $this->assertTrue($container->hasDefinition('fos_elastica.doctrine.register_listeners'));

        $definition = $container->getDefinition('fos_elastica.doctrine.register_listeners');
        $this->assertSame(RegisterListenersService::class, $definition->getClass());

        $this->assertInstanceOf(Reference::class, $definition->getArgument(0));
        $this->assertSame('event_dispatcher', (string) $definition->getArgument(0));
    }

    public function testShouldRegisterFilterObjectsListener()
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
                                    'provider' => null,
                                    'listener' => null,
                                    'finder' => null,
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ], $container);

        $this->assertTrue($container->hasDefinition('fos_elastica.filter_objects_listener'));

        $listener = $container->getDefinition('fos_elastica.filter_objects_listener');

        $this->assertSame(FilterObjectsListener::class, $listener->getClass());

        $this->assertInstanceOf(Reference::class, $listener->getArgument(0));
        $this->assertSame('fos_elastica.indexable', (string) $listener->getArgument(0));
        $this->assertEquals(['kernel.event_subscriber' => [[]]], $listener->getTags());
    }

    public function testShouldRegisterPagerPersisterRegisterService()
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
                                    'provider' => null,
                                    'listener' => null,
                                    'finder' => null,
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ], $container);

        $this->assertTrue($container->hasDefinition('fos_elastica.pager_persister_registry'));

        $listener = $container->getDefinition('fos_elastica.pager_persister_registry');
        $this->assertSame(PagerPersisterRegistry::class, $listener->getClass());
        $this->assertSame([], $listener->getArgument(0));
    }

    public function testShouldRegisterDoctrineORMListener()
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
                                    'provider' => null,
                                    'listener' => null,
                                    'finder' => null,
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ], $container);

        $this->assertTrue($container->hasDefinition('fos_elastica.listener.acme_index.acme_type'));
    }

    public function testShouldNotRegisterDoctrineORMListenerIfDisabled()
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
                                    'provider' => null,
                                    'listener' => [
                                        'enabled' => false,
                                    ],
                                    'finder' => null,
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ], $container);

        $this->assertFalse($container->hasDefinition('fos_elastica.listener.acme_index.acme_type'));
    }
}
