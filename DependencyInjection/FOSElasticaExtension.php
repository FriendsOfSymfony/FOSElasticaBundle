<?php

namespace FOS\ElasticaBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\FileLocator;
use InvalidArgumentException;

class FOSElasticaExtension extends Extension
{
    /**
     * Definition of elastica clients as configured by this extension.
     *
     * @var array
     */
    private $clients = array();

    /**
     * An array of indexes as configured by the extension.
     *
     * @var array
     */
    private $indexConfigs = array();

    /**
     * If we've encountered a type mapped to a specific persistence driver, it will be loaded
     * here.
     *
     * @var array
     */
    private $loadedDrivers = array();

    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        if (empty($config['clients']) || empty($config['indexes'])) {
            // No Clients or indexes are defined
            return;
        }

        foreach (array('config', 'index', 'persister', 'provider', 'source', 'transformer') as $basename) {
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

        $this->loadIndexes($config['indexes'], $container);
        $container->setAlias('fos_elastica.index', sprintf('fos_elastica.index.%s', $config['default_index']));

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

            $clientDef = new DefinitionDecorator('fos_elastica.client_prototype');
            $clientDef->replaceArgument(0, $clientConfig);

            $logger = $clientConfig['connections'][0]['logger'];
            if (false !== $logger) {
                $clientDef->addMethodCall('setLogger', array(new Reference($logger)));
            }

            if (isset($clientConfig['connections'][0]['connectTimeout'])) {
                $clientDef->addMethodCall('setConnectTimeout', [$clientConfig['connections'][0]['connectTimeout']]);
            }

            $clientDef->addTag('fos_elastica.client');

            $container->setDefinition($clientId, $clientDef);

            $this->clients[$name] = array(
                'id' => $clientId,
                'reference' => new Reference($clientId),
            );
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
        $indexableCallbacks = array();

        foreach ($indexes as $name => $index) {
            $indexId = sprintf('fos_elastica.index.%s', $name);
            $indexName = isset($index['index_name']) ? $index['index_name'] : $name;

            $indexDef = new DefinitionDecorator('fos_elastica.index_prototype');
            $indexDef->replaceArgument(0, $indexName);
            $indexDef->addTag('fos_elastica.index', array(
                'name' => $name,
            ));

            if (method_exists($indexDef, 'setFactory')) {
                $indexDef->setFactory(array(new Reference('fos_elastica.client'), 'getIndex'));
            } else {
                // To be removed when dependency on Symfony DependencyInjection is bumped to 2.6
                $indexDef->setFactoryService('fos_elastica.client');
                $indexDef->setFactoryMethod('getIndex');
            }

            if (isset($index['client'])) {
                $client = $this->getClient($index['client']);

                if (method_exists($indexDef, 'setFactory')) {
                    $indexDef->setFactory(array($client, 'getIndex'));
                } else {
                    // To be removed when dependency on Symfony DependencyInjection is bumped to 2.6
                    $indexDef->setFactoryService($client);
                    $indexDef->setFactoryMethod('getIndex');
                }
            }

            $container->setDefinition($indexId, $indexDef);
            $reference = new Reference($indexId);

            $this->indexConfigs[$name] = array(
                'elasticsearch_name' => $indexName,
                'reference' => $reference,
                'name' => $name,
                'settings' => $index['settings'],
                'type_prototype' => isset($index['type_prototype']) ? $index['type_prototype'] : array(),
                'use_alias' => $index['use_alias'],
            );

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
        $transformerDef = new DefinitionDecorator('fos_elastica.elastica_to_model_transformer.collection');
        $container->setDefinition($transformerId, $transformerDef);

        $finderId = sprintf('fos_elastica.finder.%s', $name);
        $finderDef = new DefinitionDecorator('fos_elastica.finder');
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
            $typeDef = new DefinitionDecorator('fos_elastica.type_prototype');
            $typeDef->replaceArgument(0, $name);

            if (method_exists($typeDef, 'setFactory')) {
                $typeDef->setFactory(array($indexConfig['reference'], 'getType'));
            } else {
                // To be removed when dependency on Symfony DependencyInjection is bumped to 2.6
                $typeDef->setFactoryService($indexConfig['reference']);
                $typeDef->setFactoryMethod('getType');
            }

            $container->setDefinition($typeId, $typeDef);

            $typeConfig = array(
                'name' => $name,
                'mapping' => array(), // An array containing anything that gets sent directly to ElasticSearch
                'config' => array(),
            );

            foreach (array(
                'dynamic_templates',
                'properties',
                '_all',
                '_boost',
                '_id',
                '_parent',
                '_routing',
                '_source',
                '_timestamp',
                '_ttl',
            ) as $field) {
                if (isset($type[$field])) {
                    $typeConfig['mapping'][$field] = $type[$field];
                }
            }

            foreach (array(
                'persistence',
                'serializer',
                'index_analyzer',
                'search_analyzer',
                'dynamic',
                'date_detection',
                'dynamic_date_formats',
                'numeric_detection',
            ) as $field) {
                $typeConfig['config'][$field] = array_key_exists($field, $type) ?
                    $type[$field] :
                    null;
            }

            $this->indexConfigs[$indexName]['types'][$name] = $typeConfig;

            if (isset($type['persistence'])) {
                $this->loadTypePersistenceIntegration($type['persistence'], $container, new Reference($typeId), $indexName, $name);

                $typeConfig['persistence'] = $type['persistence'];
            }

            if (isset($type['indexable_callback'])) {
                $indexableCallbacks[sprintf('%s/%s', $indexName, $name)] = $type['indexable_callback'];
            }

            if ($container->hasDefinition('fos_elastica.serializer_callback_prototype')) {
                $typeSerializerId = sprintf('%s.serializer.callback', $typeId);
                $typeSerializerDef = new DefinitionDecorator('fos_elastica.serializer_callback_prototype');

                if (isset($type['serializer']['groups'])) {
                    $typeSerializerDef->addMethodCall('setGroups', array($type['serializer']['groups']));
                }

                if (isset($type['serializer']['serialize_null'])) {
                    $typeSerializerDef->addMethodCall('setSerializeNull', array($type['serializer']['serialize_null']));
                }

                if (isset($type['serializer']['version'])) {
                    $typeSerializerDef->addMethodCall('setVersion', array($type['serializer']['version']));
                }

                $typeDef->addMethodCall('setSerializer', array(array(new Reference($typeSerializerId), 'serialize')));
                $container->setDefinition($typeSerializerId, $typeSerializerDef);
            }
        }
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
            $this->loadTypeProvider($typeConfig, $container, $objectPersisterId, $indexName, $typeName);
        }
        if (isset($typeConfig['finder'])) {
            $this->loadTypeFinder($typeConfig, $container, $elasticaToModelTransformerId, $typeRef, $indexName, $typeName);
        }
        if (isset($typeConfig['listener'])) {
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
        $serviceDef = new DefinitionDecorator($abstractId);
        $serviceDef->addTag('fos_elastica.elastica_to_model_transformer', array('type' => $typeName, 'index' => $indexName));

        // Doctrine has a mandatory service as first argument
        $argPos = ('propel' === $typeConfig['driver']) ? 0 : 1;

        $serviceDef->replaceArgument($argPos, $typeConfig['model']);
        $serviceDef->replaceArgument($argPos + 1, array_merge($typeConfig['elastica_to_model_transformer'], array(
            'identifier' => $typeConfig['identifier'],
        )));
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
        $serviceDef = new DefinitionDecorator($abstractId);
        $serviceDef->replaceArgument(0, array(
            'identifier' => $typeConfig['identifier'],
        ));
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

        $arguments = array(
            $typeRef,
            new Reference($transformerId),
            $typeConfig['model'],
        );

        if ($container->hasDefinition('fos_elastica.serializer_callback_prototype')) {
            $abstractId = 'fos_elastica.object_serializer_persister';
            $callbackId = sprintf('%s.%s.serializer.callback', $this->indexConfigs[$indexName]['reference'], $typeName);
            $arguments[] = array(new Reference($callbackId), 'serialize');
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
        $serviceDef = new DefinitionDecorator($abstractId);
        foreach ($arguments as $i => $argument) {
            $serviceDef->replaceArgument($i, $argument);
        }

        $container->setDefinition($serviceId, $serviceDef);

        return $serviceId;
    }

    /**
     * Loads a provider for a type.
     *
     * @param array            $typeConfig
     * @param ContainerBuilder $container
     * @param string           $objectPersisterId
     * @param string           $indexName
     * @param string           $typeName
     *
     * @return string
     */
    private function loadTypeProvider(array $typeConfig, ContainerBuilder $container, $objectPersisterId, $indexName, $typeName)
    {
        if (isset($typeConfig['provider']['service'])) {
            return $typeConfig['provider']['service'];
        }

        /* Note: provider services may conflict with "prototype.driver", if the
         * index and type names were "prototype" and a driver, respectively.
         */
        $providerId = sprintf('fos_elastica.provider.%s.%s', $indexName, $typeName);
        $providerDef = new DefinitionDecorator('fos_elastica.provider.prototype.'.$typeConfig['driver']);
        $providerDef->addTag('fos_elastica.provider', array('index' => $indexName, 'type' => $typeName));
        $providerDef->replaceArgument(0, new Reference($objectPersisterId));
        $providerDef->replaceArgument(2, $typeConfig['model']);
        // Propel provider can simply ignore Doctrine-specific options
        $providerDef->replaceArgument(3, array_merge(array_diff_key($typeConfig['provider'], array('service' => 1)), array(
            'indexName' => $indexName,
            'typeName' => $typeName,
        )));
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
        $listenerDef = new DefinitionDecorator($abstractListenerId);
        $listenerDef->replaceArgument(0, new Reference($objectPersisterId));
        $listenerDef->replaceArgument(2, array(
            'identifier' => $typeConfig['identifier'],
            'indexName' => $indexName,
            'typeName' => $typeName,
        ));
        $listenerDef->replaceArgument(3, $typeConfig['listener']['logger'] ?
            new Reference($typeConfig['listener']['logger']) :
            null
        );

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

        if (null !== $tagName) {
            foreach ($this->getDoctrineEvents($typeConfig) as $event) {
                $listenerDef->addTag($tagName, array('event' => $event));
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
                throw new InvalidArgumentException(sprintf('Cannot determine events for driver "%s"', $typeConfig['driver']));
        }

        $events = array();
        $eventMapping = array(
            'insert' => array(constant($eventsClass.'::postPersist')),
            'update' => array(constant($eventsClass.'::postUpdate')),
            'delete' => array(constant($eventsClass.'::preRemove')),
            'flush' => array($typeConfig['listener']['immediate'] ? constant($eventsClass.'::preFlush') : constant($eventsClass.'::postFlush')),
        );

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
            $finderDef = new DefinitionDecorator('fos_elastica.finder');
            $finderDef->replaceArgument(0, $typeRef);
            $finderDef->replaceArgument(1, new Reference($elasticaToModelId));
            $container->setDefinition($finderId, $finderDef);
        }

        $managerId = sprintf('fos_elastica.manager.%s', $typeConfig['driver']);
        $managerDef = $container->getDefinition($managerId);
        $arguments = array( $typeConfig['model'], new Reference($finderId));
        if (isset($typeConfig['repository'])) {
            $arguments[] = $typeConfig['repository'];
        }
        $managerDef->addMethodCall('addEntity', $arguments);

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

        $callbackClassImplementedInterfaces = class_implements($config['callback_class']);
        if (isset($callbackClassImplementedInterfaces['Symfony\Component\DependencyInjection\ContainerAwareInterface'])) {
            $serializer->addMethodCall('setContainer', array(new Reference('service_container')));
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
            throw new InvalidArgumentException(sprintf('The elastica client with name "%s" is not defined', $clientName));
        }

        return $this->clients[$clientName]['reference'];
    }
}
