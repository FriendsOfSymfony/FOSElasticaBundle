<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\DependencyInjection;

use FOS\ElasticaBundle\Elastica\Client;
use FOS\ElasticaBundle\Manager\RepositoryManagerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class FOSElasticaExtension extends Extension
{
    /**
     * Definition of elastica clients as configured by this extension.
     *
     * @var array
     */
    private $clients = [];

    /**
     * An array of indexes as configured by the extension.
     *
     * @var array
     */
    private $indexConfigs = [];

    /**
     * If we've encountered a type mapped to a specific persistence driver, it will be loaded
     * here.
     *
     * @var array
     */
    private $loadedDrivers = [];

    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        if (empty($config['clients']) || empty($config['indexes'])) {
            // No Clients or indexes are defined
            return;
        }

        foreach (['config', 'index', 'persister', 'provider', 'source', 'transformer', 'event_listener', 'commands'] as $basename) {
            $loader->load(sprintf('%s.xml', $basename));
        }

        if (empty($config['default_client'])) {
            $keys = array_keys($config['clients']);
            $config['default_client'] = reset($keys);
        }

        if (empty($config['default_index'])) {
            $keys = array_keys($config['indexes']);
            $config['default_index'] = reset($keys);
        }

        if (isset($config['serializer'])) {
            $loader->load('serializer.xml');

            $this->loadSerializer($config['serializer'], $container);
        }

        $this->loadClients($config['clients'], $container);
        $container->setAlias('fos_elastica.client', sprintf('fos_elastica.client.%s', $config['default_client']));
        $container->getAlias('fos_elastica.client')->setPublic(true);
        $container->setAlias(Client::class, 'fos_elastica.client');
        $container->getAlias(Client::class)->setPublic(false);

        $this->loadIndexes($config['indexes'], $container);
        $container->setAlias('fos_elastica.index', sprintf('fos_elastica.index.%s', $config['default_index']));
        $container->getAlias('fos_elastica.index')->setPublic(true);
        $container->setParameter('fos_elastica.default_index', $config['default_index']);

        $container->getDefinition('fos_elastica.config_source.container')->replaceArgument(0, $this->indexConfigs);

        $this->loadIndexManager($container);

        $this->createDefaultManagerAlias($config['default_manager'], $container);
    }

    /**
     * @param array            $config
     * @param ContainerBuilder $container
     *
     * @return Configuration
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration($container->getParameter('kernel.debug'));
    }

    /**
     * Loads the configured clients.
     *
     * @param array            $clients   An array of clients configurations
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @return array
     */
    private function loadClients(array $clients, ContainerBuilder $container)
    {
        foreach ($clients as $name => $clientConfig) {
            $clientId = sprintf('fos_elastica.client.%s', $name);

            $clientDef = new ChildDefinition('fos_elastica.client_prototype');
            $clientDef->replaceArgument(0, $clientConfig);

            $logger = $clientConfig['connections'][0]['logger'];
            if (false !== $logger) {
                $clientDef->addMethodCall('setLogger', [new Reference($logger)]);
            }

            $clientDef->addTag('fos_elastica.client');

            $container->setDefinition($clientId, $clientDef);

            $this->clients[$name] = [
                'id' => $clientId,
                'reference' => new Reference($clientId),
            ];
        }
    }

    /**
     * Loads the configured indexes.
     *
     * @param array            $indexes   An array of indexes configurations
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    private function loadIndexes(array $indexes, ContainerBuilder $container)
    {
        $indexableCallbacks = [];

        foreach ($indexes as $name => $index) {
            $indexId = sprintf('fos_elastica.index.%s', $name);
            $indexName = isset($index['index_name']) ? $index['index_name'] : $name;

            $indexDef = new ChildDefinition('fos_elastica.index_prototype');
            $indexDef->setFactory([new Reference('fos_elastica.client'), 'getIndex']);
            $indexDef->replaceArgument(0, $indexName);
            $indexDef->addTag('fos_elastica.index', [
                'name' => $name,
            ]);

            if (isset($index['client'])) {
                $client = $this->getClient($index['client']);

                $indexDef->setFactory([$client, 'getIndex']);
            }

            $container->setDefinition($indexId, $indexDef);
            $reference = new Reference($indexId);

            $this->indexConfigs[$name] = [
                'elasticsearch_name' => $indexName,
                'reference' => $reference,
                'name' => $name,
                'settings' => $index['settings'],
                'type_prototype' => isset($index['type_prototype']) ? $index['type_prototype'] : [],
                'use_alias' => $index['use_alias'],
            ];

            if ($index['finder']) {
                $this->loadIndexFinder($container, $name, $reference);
            }

            $this->loadTypes((array) $index['types'], $container, $this->indexConfigs[$name], $indexableCallbacks);
        }

        $indexable = $container->getDefinition('fos_elastica.indexable');
        $indexable->replaceArgument(0, $indexableCallbacks);
    }

    /**
     * Loads the configured index finders.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param string                                                  $name      The index name
     * @param Reference                                               $index     Reference to the related index
     *
     * @return string
     */
    private function loadIndexFinder(ContainerBuilder $container, $name, Reference $index)
    {
        /* Note: transformer services may conflict with "collection.index", if
         * an index and type names were "collection" and an index, respectively.
         */
        $transformerId = sprintf('fos_elastica.elastica_to_model_transformer.collection.%s', $name);
        $transformerDef = new ChildDefinition('fos_elastica.elastica_to_model_transformer.collection');
        $container->setDefinition($transformerId, $transformerDef);

        $finderId = sprintf('fos_elastica.finder.%s', $name);
        $finderDef = new ChildDefinition('fos_elastica.finder');
        $finderDef->replaceArgument(0, $index);
        $finderDef->replaceArgument(1, new Reference($transformerId));

        $container->setDefinition($finderId, $finderDef);
    }

    /**
     * Loads the configured types.
     *
     * @param array            $types
     * @param ContainerBuilder $container
     * @param array            $indexConfig
     * @param array            $indexableCallbacks
     */
    private function loadTypes(array $types, ContainerBuilder $container, array $indexConfig, array &$indexableCallbacks)
    {
        foreach ($types as $name => $type) {
            $indexName = $indexConfig['name'];

            $typeId = sprintf('%s.%s', $indexConfig['reference'], $name);
            $typeDef = new ChildDefinition('fos_elastica.type_prototype');
            $typeDef->setFactory([$indexConfig['reference'], 'getType']);
            $typeDef->replaceArgument(0, $name);

            $container->setDefinition($typeId, $typeDef);

            $typeConfig = [
                'name' => $name,
                'mapping' => [], // An array containing anything that gets sent directly to ElasticSearch
                'config' => [],
            ];

            foreach ([
                'dynamic_templates',
                'properties',
                '_all',
                '_id',
                '_parent',
                '_routing',
                '_source',
            ] as $field) {
                if (isset($type[$field])) {
                    $typeConfig['mapping'][$field] = $type[$field];
                }
            }

            foreach ([
                'persistence',
                'serializer',
                'analyzer',
                'search_analyzer',
                'dynamic',
                'date_detection',
                'dynamic_date_formats',
                'numeric_detection',
            ] as $field) {
                $typeConfig['config'][$field] = array_key_exists($field, $type) ?
                    $type[$field] :
                    null;
            }

            $this->indexConfigs[$indexName]['types'][$name] = $typeConfig;

            if (isset($type['persistence'])) {
                $this->loadTypePersistenceIntegration($type['persistence'], $container, new Reference($typeId), $indexName, $name);

                $typeConfig['persistence'] = $type['persistence'];
            }

            if (isset($type['_parent'])) {
                // _parent mapping cannot contain `property` and `identifier`, so removing them after building `persistence`
                unset($indexConfig['types'][$name]['mapping']['_parent']['property'], $indexConfig['types'][$name]['mapping']['_parent']['identifier']);
            }

            if (isset($type['indexable_callback'])) {
                $indexableCallbacks[sprintf('%s/%s', $indexName, $name)] = $this->buildCallback($type['indexable_callback'], $name);
            }

            if ($container->hasDefinition('fos_elastica.serializer_callback_prototype')) {
                $typeSerializerId = sprintf('%s.serializer.callback', $typeId);
                $typeSerializerDef = new ChildDefinition('fos_elastica.serializer_callback_prototype');

                if (isset($type['serializer']['groups'])) {
                    $typeSerializerDef->addMethodCall('setGroups', [$type['serializer']['groups']]);
                }

                if (isset($type['serializer']['serialize_null'])) {
                    $typeSerializerDef->addMethodCall('setSerializeNull', [$type['serializer']['serialize_null']]);
                }

                if (isset($type['serializer']['version'])) {
                    $typeSerializerDef->addMethodCall('setVersion', [$type['serializer']['version']]);
                }

                $typeDef->addMethodCall('setSerializer', [[new Reference($typeSerializerId), 'serialize']]);
                $container->setDefinition($typeSerializerId, $typeSerializerDef);
            }
        }
    }

    private function buildCallback($indexCallback, $typeName)
    {
        if (is_array($indexCallback)) {
            if (!isset($indexCallback[0])) {
                throw new \InvalidArgumentException(sprintf('Invalid indexable_callback for type %s'), $typeName);
            }

            $classOrServiceRef = $this->transformServiceReference($indexCallback[0]);
            if ($classOrServiceRef instanceof Reference && !isset($indexCallback[1])) {
                return $classOrServiceRef; // __invoke
            }

            if (!isset($indexCallback[1])) {
                throw new \InvalidArgumentException(sprintf('Invalid indexable_callback for type %s'), $typeName);
            }

            return [$classOrServiceRef, $indexCallback[1]];
        };

        if (is_string($indexCallback)) {
            return $this->transformServiceReference($indexCallback);
        }

        throw new \InvalidArgumentException(sprintf('Invalid indexable_callback for type %s'), $typeName);
    }

    private function transformServiceReference($classOrService)
    {
        return 0 === strpos($classOrService, '@') ? new Reference(substr($classOrService, 1)) : $classOrService;
    }

    /**
     * Loads the optional provider and finder for a type.
     *
     * @param array            $typeConfig
     * @param ContainerBuilder $container
     * @param Reference        $typeRef
     * @param string           $indexName
     * @param string           $typeName
     */
    private function loadTypePersistenceIntegration(array $typeConfig, ContainerBuilder $container, Reference $typeRef, $indexName, $typeName)
    {
        if (isset($typeConfig['driver'])) {
            $this->loadDriver($container, $typeConfig['driver']);
        }

        $elasticaToModelTransformerId = $this->loadElasticaToModelTransformer($typeConfig, $container, $indexName, $typeName);
        $modelToElasticaTransformerId = $this->loadModelToElasticaTransformer($typeConfig, $container, $indexName, $typeName);
        $objectPersisterId = $this->loadObjectPersister($typeConfig, $typeRef, $container, $indexName, $typeName, $modelToElasticaTransformerId);

        if (isset($typeConfig['provider'])) {
            $this->loadTypePagerProvider($typeConfig, $container, $indexName, $typeName);
        }
        if (isset($typeConfig['finder'])) {
            $this->loadTypeFinder($typeConfig, $container, $elasticaToModelTransformerId, $typeRef, $indexName, $typeName);
        }
        if (isset($typeConfig['listener']) && $typeConfig['listener']['enabled']) {
            $this->loadTypeListener($typeConfig, $container, $objectPersisterId, $indexName, $typeName);
        }
    }

    /**
     * Creates and loads an ElasticaToModelTransformer.
     *
     * @param array            $typeConfig
     * @param ContainerBuilder $container
     * @param string           $indexName
     * @param string           $typeName
     *
     * @return string
     */
    private function loadElasticaToModelTransformer(array $typeConfig, ContainerBuilder $container, $indexName, $typeName)
    {
        if (isset($typeConfig['elastica_to_model_transformer']['service'])) {
            return $typeConfig['elastica_to_model_transformer']['service'];
        }

        /* Note: transformer services may conflict with "prototype.driver", if
         * the index and type names were "prototype" and a driver, respectively.
         */
        $abstractId = sprintf('fos_elastica.elastica_to_model_transformer.prototype.%s', $typeConfig['driver']);
        $serviceId = sprintf('fos_elastica.elastica_to_model_transformer.%s.%s', $indexName, $typeName);
        $serviceDef = new ChildDefinition($abstractId);
        $serviceDef->addTag('fos_elastica.elastica_to_model_transformer', ['type' => $typeName, 'index' => $indexName]);

        // Doctrine has a mandatory service as first argument
        $argPos = ('propel' === $typeConfig['driver']) ? 0 : 1;

        $serviceDef->replaceArgument($argPos, $typeConfig['model']);
        $serviceDef->replaceArgument($argPos + 1, array_merge($typeConfig['elastica_to_model_transformer'], [
            'identifier' => $typeConfig['identifier'],
        ]));
        $container->setDefinition($serviceId, $serviceDef);

        return $serviceId;
    }

    /**
     * Creates and loads a ModelToElasticaTransformer for an index/type.
     *
     * @param array            $typeConfig
     * @param ContainerBuilder $container
     * @param string           $indexName
     * @param string           $typeName
     *
     * @return string
     */
    private function loadModelToElasticaTransformer(array $typeConfig, ContainerBuilder $container, $indexName, $typeName)
    {
        if (isset($typeConfig['model_to_elastica_transformer']['service'])) {
            return $typeConfig['model_to_elastica_transformer']['service'];
        }

        $abstractId = $container->hasDefinition('fos_elastica.serializer_callback_prototype') ?
            'fos_elastica.model_to_elastica_identifier_transformer' :
            'fos_elastica.model_to_elastica_transformer';

        $serviceId = sprintf('fos_elastica.model_to_elastica_transformer.%s.%s', $indexName, $typeName);
        $serviceDef = new ChildDefinition($abstractId);
        $serviceDef->replaceArgument(0, [
            'identifier' => $typeConfig['identifier'],
        ]);
        $container->setDefinition($serviceId, $serviceDef);

        return $serviceId;
    }

    /**
     * Creates and loads an object persister for a type.
     *
     * @param array            $typeConfig
     * @param Reference        $typeRef
     * @param ContainerBuilder $container
     * @param string           $indexName
     * @param string           $typeName
     * @param string           $transformerId
     *
     * @return string
     */
    private function loadObjectPersister(array $typeConfig, Reference $typeRef, ContainerBuilder $container, $indexName, $typeName, $transformerId)
    {
        if (isset($typeConfig['persister']['service'])) {
            return $typeConfig['persister']['service'];
        }

        $arguments = [
            $typeRef,
            new Reference($transformerId),
            $typeConfig['model'],
        ];

        if ($container->hasDefinition('fos_elastica.serializer_callback_prototype')) {
            $abstractId = 'fos_elastica.object_serializer_persister';
            $callbackId = sprintf('%s.%s.serializer.callback', $this->indexConfigs[$indexName]['reference'], $typeName);
            $arguments[] = [new Reference($callbackId), 'serialize'];
        } else {
            $abstractId = 'fos_elastica.object_persister';
            $mapping = $this->indexConfigs[$indexName]['types'][$typeName]['mapping'];
            $argument = $mapping['properties'];
            if (isset($mapping['_parent'])) {
                $argument['_parent'] = $mapping['_parent'];
            }
            $arguments[] = $argument;
        }

        $serviceId = sprintf('fos_elastica.object_persister.%s.%s', $indexName, $typeName);
        $serviceDef = new ChildDefinition($abstractId);
        foreach ($arguments as $i => $argument) {
            $serviceDef->replaceArgument($i, $argument);
        }

        $serviceDef->addTag('fos_elastica.persister', ['index' => $indexName, 'type' => $typeName]);

        $container->setDefinition($serviceId, $serviceDef);

        return $serviceId;
    }

    /**
     * Loads a pager provider for a type.
     *
     * @param array            $typeConfig
     * @param ContainerBuilder $container
     * @param string           $indexName
     * @param string           $typeName
     *
     * @return string
     */
    private function loadTypePagerProvider(array $typeConfig, ContainerBuilder $container, $indexName, $typeName)
    {
        if (isset($typeConfig['provider']['service'])) {
            return $typeConfig['provider']['service'];
        }

        $baseConfig = $typeConfig['provider'];
        unset($baseConfig['service']);

        $driver = $typeConfig['driver'];

        switch ($driver) {
            case 'orm':
                $providerDef = new ChildDefinition('fos_elastica.pager_provider.prototype.'.$driver);
                $providerDef->replaceArgument(2, $typeConfig['model']);
                $providerDef->replaceArgument(3, $baseConfig);

                break;
            case 'mongodb':
                $providerDef = new ChildDefinition('fos_elastica.pager_provider.prototype.'.$driver);
                $providerDef->replaceArgument(2, $typeConfig['model']);
                $providerDef->replaceArgument(3, $baseConfig);

                break;
            case 'phpcr':
                $providerDef = new ChildDefinition('fos_elastica.pager_provider.prototype.'.$driver);
                $providerDef->replaceArgument(2, $typeConfig['model']);
                $providerDef->replaceArgument(3, $baseConfig);

                break;
            case 'propel':
                $providerDef = new ChildDefinition('fos_elastica.pager_provider.prototype.'.$driver);
                $providerDef->replaceArgument(0, $typeConfig['model']);
                $providerDef->replaceArgument(1, $baseConfig);

                break;
            default:
                throw new \LogicException(sprintf('The pager provider for driver "%s" does not exist.', $driver));
        }

        /* Note: provider services may conflict with "prototype.driver", if the
         * index and type names were "prototype" and a driver, respectively.
         */
        $providerId = sprintf('fos_elastica.pager_provider.%s.%s', $indexName, $typeName);
        $providerDef->addTag('fos_elastica.pager_provider', ['index' => $indexName, 'type' => $typeName]);

        $container->setDefinition($providerId, $providerDef);

        return $providerId;
    }

    /**
     * Loads doctrine listeners to handle indexing of new or updated objects.
     *
     * @param array            $typeConfig
     * @param ContainerBuilder $container
     * @param string           $objectPersisterId
     * @param string           $indexName
     * @param string           $typeName
     *
     * @return string
     */
    private function loadTypeListener(array $typeConfig, ContainerBuilder $container, $objectPersisterId, $indexName, $typeName)
    {
        if (isset($typeConfig['listener']['service'])) {
            return $typeConfig['listener']['service'];
        }

        /* Note: listener services may conflict with "prototype.driver", if the
         * index and type names were "prototype" and a driver, respectively.
         */
        $abstractListenerId = sprintf('fos_elastica.listener.prototype.%s', $typeConfig['driver']);
        $listenerId = sprintf('fos_elastica.listener.%s.%s', $indexName, $typeName);
        $listenerDef = new ChildDefinition($abstractListenerId);
        $listenerDef->replaceArgument(0, new Reference($objectPersisterId));
        $listenerDef->replaceArgument(3, $typeConfig['listener']['logger'] ?
            new Reference($typeConfig['listener']['logger']) :
            null
        );
        $listenerConfig = [
            'identifier' => $typeConfig['identifier'],
            'indexName' => $indexName,
            'typeName' => $typeName,
        ];

        $tagName = null;
        switch ($typeConfig['driver']) {
            case 'orm':
                $tagName = 'doctrine.event_listener';
                break;
            case 'phpcr':
                $tagName = 'doctrine_phpcr.event_listener';
                break;
            case 'mongodb':
                $tagName = 'doctrine_mongodb.odm.event_listener';
                break;
        }

        if ($typeConfig['listener']['defer']) {
            $listenerDef->setPublic(true);
            $listenerDef->addTag(
                'kernel.event_listener',
                ['event' => 'kernel.terminate', 'method' => 'onTerminate']
            );
            $listenerDef->addTag(
                'kernel.event_listener',
                ['event' => 'console.terminate', 'method' => 'onTerminate']
            );
            $listenerConfig['defer'] = true;
        }

        $listenerDef->replaceArgument(2, $listenerConfig);

        if (null !== $tagName) {
            foreach ($this->getDoctrineEvents($typeConfig) as $event) {
                $listenerDef->addTag($tagName, ['event' => $event]);
            }
        }

        $container->setDefinition($listenerId, $listenerDef);

        return $listenerId;
    }

    /**
     * Map Elastica to Doctrine events for the current driver.
     */
    private function getDoctrineEvents(array $typeConfig)
    {
        switch ($typeConfig['driver']) {
            case 'orm':
                $eventsClass = '\Doctrine\ORM\Events';
                break;
            case 'phpcr':
                $eventsClass = '\Doctrine\ODM\PHPCR\Event';
                break;
            case 'mongodb':
                $eventsClass = '\Doctrine\ODM\MongoDB\Events';
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Cannot determine events for driver "%s"', $typeConfig['driver']));
        }

        $events = [];
        $eventMapping = [
            'insert' => [constant($eventsClass.'::postPersist')],
            'update' => [constant($eventsClass.'::postUpdate')],
            'delete' => [constant($eventsClass.'::preRemove')],
            'flush' => [constant($eventsClass.'::postFlush')],
        ];

        foreach ($eventMapping as $event => $doctrineEvents) {
            if (isset($typeConfig['listener'][$event]) && $typeConfig['listener'][$event]) {
                $events = array_merge($events, $doctrineEvents);
            }
        }

        return $events;
    }

    /**
     * Loads a Type specific Finder.
     *
     * @param array            $typeConfig
     * @param ContainerBuilder $container
     * @param string           $elasticaToModelId
     * @param Reference        $typeRef
     * @param string           $indexName
     * @param string           $typeName
     *
     * @return string
     */
    private function loadTypeFinder(array $typeConfig, ContainerBuilder $container, $elasticaToModelId, Reference $typeRef, $indexName, $typeName)
    {
        if (isset($typeConfig['finder']['service'])) {
            $finderId = $typeConfig['finder']['service'];
        } else {
            $finderId = sprintf('fos_elastica.finder.%s.%s', $indexName, $typeName);
            $finderDef = new ChildDefinition('fos_elastica.finder');
            $finderDef->replaceArgument(0, $typeRef);
            $finderDef->replaceArgument(1, new Reference($elasticaToModelId));
            $container->setDefinition($finderId, $finderDef);
        }

        $indexTypeName = "$indexName/$typeName";
        $arguments = [$indexTypeName, new Reference($finderId)];
        if (isset($typeConfig['repository'])) {
            $arguments[] = $typeConfig['repository'];
        }

        $container->getDefinition('fos_elastica.repository_manager')
            ->addMethodCall('addType', $arguments);

        $managerId = sprintf('fos_elastica.manager.%s', $typeConfig['driver']);
        $container->getDefinition($managerId)
            ->addMethodCall('addEntity', [$typeConfig['model'], $indexTypeName]);

        return $finderId;
    }

    /**
     * Loads the index manager.
     *
     * @param ContainerBuilder $container
     **/
    private function loadIndexManager(ContainerBuilder $container)
    {
        $indexRefs = array_map(function ($index) {
            return $index['reference'];
        }, $this->indexConfigs);

        $managerDef = $container->getDefinition('fos_elastica.index_manager');
        $managerDef->replaceArgument(0, $indexRefs);
    }

    /**
     * Makes sure a specific driver has been loaded.
     *
     * @param ContainerBuilder $container
     * @param string           $driver
     */
    private function loadDriver(ContainerBuilder $container, $driver)
    {
        if (in_array($driver, $this->loadedDrivers)) {
            return;
        }

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load($driver.'.xml');
        $this->loadedDrivers[] = $driver;
    }

    /**
     * Loads and configures the serializer prototype.
     *
     * @param array            $config
     * @param ContainerBuilder $container
     */
    private function loadSerializer($config, ContainerBuilder $container)
    {
        $container->setAlias('fos_elastica.serializer', $config['serializer']);

        $serializer = $container->getDefinition('fos_elastica.serializer_callback_prototype');
        $serializer->setClass($config['callback_class']);

        if (is_subclass_of($config['callback_class'], ContainerAwareInterface::class)) {
            $serializer->addMethodCall('setContainer', [new Reference('service_container')]);
        }
    }

    /**
     * Creates a default manager alias for defined default manager or the first loaded driver.
     *
     * @param string           $defaultManager
     * @param ContainerBuilder $container
     */
    private function createDefaultManagerAlias($defaultManager, ContainerBuilder $container)
    {
        if (0 == count($this->loadedDrivers)) {
            return;
        }

        if (count($this->loadedDrivers) > 1
            && in_array($defaultManager, $this->loadedDrivers)
        ) {
            $defaultManagerService = $defaultManager;
        } else {
            $defaultManagerService = $this->loadedDrivers[0];
        }

        $container->setAlias('fos_elastica.manager', sprintf('fos_elastica.manager.%s', $defaultManagerService));
        $container->getAlias('fos_elastica.manager')->setPublic(true);
        $container->setAlias(RepositoryManagerInterface::class, 'fos_elastica.manager');
        $container->getAlias(RepositoryManagerInterface::class)->setPublic(false);
    }

    /**
     * Returns a reference to a client given its configured name.
     *
     * @param string $clientName
     *
     * @return Reference
     *
     * @throws \InvalidArgumentException
     */
    private function getClient($clientName)
    {
        if (!array_key_exists($clientName, $this->clients)) {
            throw new \InvalidArgumentException(sprintf('The elastica client with name "%s" is not defined', $clientName));
        }

        return $this->clients[$clientName]['reference'];
    }
}
