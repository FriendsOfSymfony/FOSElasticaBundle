<?php

namespace FOS\ElasticaBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\FileLocator;
use InvalidArgumentException;

class FOSElasticaExtension extends Extension
{
    protected $indexConfigs     = array();
    protected $typeFields       = array();
    protected $loadedDrivers    = array();

    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config        = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('config.xml');

        if (empty($config['clients']) || empty($config['indexes'])) {
            throw new InvalidArgumentException('You must define at least one client and one index');
        }

        if (empty($config['default_client'])) {
            $keys = array_keys($config['clients']);
            $config['default_client'] = reset($keys);
        }

        if (empty($config['default_index'])) {
            $keys = array_keys($config['indexes']);
            $config['default_index'] = reset($keys);
        }

        $clientIdsByName = $this->loadClients($config['clients'], $container);
        $serializerConfig = isset($config['serializer']) ? $config['serializer'] : null;
        $indexIdsByName  = $this->loadIndexes($config['indexes'], $container, $clientIdsByName, $config['default_client'], $serializerConfig);
        $indexRefsByName = array_map(function($id) {
            return new Reference($id);
        }, $indexIdsByName);

        $this->loadIndexManager($indexRefsByName, $container);
        $this->loadResetter($this->indexConfigs, $container);

        $container->setAlias('fos_elastica.client', sprintf('fos_elastica.client.%s', $config['default_client']));
        $container->setAlias('fos_elastica.index', sprintf('fos_elastica.index.%s', $config['default_index']));

        $this->createDefaultManagerAlias($config['default_manager'], $container);
    }

    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration($config);
    }

    /**
     * Loads the configured clients.
     *
     * @param array $clients An array of clients configurations
     * @param ContainerBuilder $container A ContainerBuilder instance
     * @return array
     */
    protected function loadClients(array $clients, ContainerBuilder $container)
    {
        $clientIds = array();
        foreach ($clients as $name => $clientConfig) {
            $clientDef = $container->getDefinition('fos_elastica.client');
            $clientDef->replaceArgument(0, $clientConfig);

            $clientId = sprintf('fos_elastica.client.%s', $name);

            $container->setDefinition($clientId, $clientDef);

            $clientIds[$name] = $clientId;
        }

        return $clientIds;
    }

    /**
     * Loads the configured indexes.
     *
     * @param array $indexes An array of indexes configurations
     * @param ContainerBuilder $container A ContainerBuilder instance
     * @param array $clientIdsByName
     * @param $defaultClientName
     * @throws \InvalidArgumentException
     * @return array
     */
    protected function loadIndexes(array $indexes, ContainerBuilder $container, array $clientIdsByName, $defaultClientName, $serializerConfig)
    {
        $indexIds = array();
        foreach ($indexes as $name => $index) {
            if (isset($index['client'])) {
                $clientName = $index['client'];
                if (!isset($clientIdsByName[$clientName])) {
                    throw new InvalidArgumentException(sprintf('The elastica client with name "%s" is not defined', $clientName));
                }
            } else {
                $clientName = $defaultClientName;
            }

            $clientId = $clientIdsByName[$clientName];
            $serviceSuffix = isset($index['service_suffix']) ? $index['service_suffix'] : $name;
            $indexId = sprintf('fos_elastica.index.%s', $serviceSuffix);
            $indexName = isset($index['index_name']) ? $index['index_name'] : $name;
            $indexDefArgs = array($indexName);
            $indexDef = new Definition('%fos_elastica.index.class%', $indexDefArgs);
            $indexDef->setFactoryService($clientId);
            $indexDef->setFactoryMethod('getIndex');
            $container->setDefinition($indexId, $indexDef);
            $typePrototypeConfig = isset($index['type_prototype']) ? $index['type_prototype'] : array();
            $indexIds[$name] = $indexId;
            $this->indexConfigs[$name] = array(
                'index' => new Reference($indexId),
                'config' => array(
                    'mappings' => array()
                )
            );
            if ($index['finder']) {
                $this->loadIndexFinder($container, $name, $indexId);
            }
            if (!empty($index['settings'])) {
                $this->indexConfigs[$name]['config']['settings'] = $index['settings'];
            }
            $this->loadTypes(isset($index['types']) ? $index['types'] : array(), $container, $name, $indexId, $typePrototypeConfig, $serializerConfig);
        }

        return $indexIds;
    }

    /**
     * Loads the configured index finders.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param string $name The index name
     * @param string $indexId The index service identifier
     * @return string
     */
    protected function loadIndexFinder(ContainerBuilder $container, $name, $indexId)
    {
        $abstractTransformerId = 'fos_elastica.elastica_to_model_transformer.collection.prototype';
        $transformerId = sprintf('fos_elastica.elastica_to_model_transformer.collection.%s', $name);
        $transformerDef = new DefinitionDecorator($abstractTransformerId);
        $container->setDefinition($transformerId, $transformerDef);

        $abstractFinderId = 'fos_elastica.finder.prototype';
        $finderId = sprintf('fos_elastica.finder.%s', $name);
        $finderDef = new DefinitionDecorator($abstractFinderId);
        $finderDef->replaceArgument(0, new Reference($indexId));
        $finderDef->replaceArgument(1, new Reference($transformerId));

        $container->setDefinition($finderId, $finderDef);

        return $finderId;
    }

    /**
     * Loads the configured types.
     *
     * @param array $types An array of types configurations
     * @param ContainerBuilder $container A ContainerBuilder instance
     * @param $indexName
     * @param $indexId
     * @param array $typePrototypeConfig
     */
    protected function loadTypes(array $types, ContainerBuilder $container, $indexName, $indexId, array $typePrototypeConfig, $serializerConfig)
    {
        foreach ($types as $name => $type) {
            $type = self::deepArrayUnion($typePrototypeConfig, $type);
            $typeId = sprintf('%s.%s', $indexId, $name);
            $typeDefArgs = array($name);
            $typeDef = new Definition('%fos_elastica.type.class%', $typeDefArgs);
            $typeDef->setFactoryService($indexId);
            $typeDef->setFactoryMethod('getType');
            if ($serializerConfig) {
                $callbackDef = new Definition($serializerConfig['callback_class']);
                $callbackId = sprintf('%s.%s.serializer.callback', $indexId, $name);

                $typeDef->addMethodCall('setSerializer', array(array(new Reference($callbackId), 'serialize')));
                $callbackDef->addMethodCall('setSerializer', array(new Reference($serializerConfig['serializer'])));
                if (isset($type['serializer']['groups'])) {
                    $callbackDef->addMethodCall('setGroups', array($type['serializer']['groups']));
                }
                if (isset($type['serializer']['version'])) {
                    $callbackDef->addMethodCall('setVersion', array($type['serializer']['version']));
                }

                $container->setDefinition($callbackId, $callbackDef);

                $typeDef->addMethodCall('setSerializer', array(array(new Reference($callbackId), 'serialize')));
            }
            $container->setDefinition($typeId, $typeDef);
            if (isset($type['_id'])) {
                $this->indexConfigs[$indexName]['config']['mappings'][$name]['_id'] = $type['_id'];
            }
            if (isset($type['_source'])) {
                $this->indexConfigs[$indexName]['config']['mappings'][$name]['_source'] = $type['_source'];
            }
            if (isset($type['_boost'])) {
                $this->indexConfigs[$indexName]['config']['mappings'][$name]['_boost'] = $type['_boost'];
            }
            if (isset($type['_routing'])) {
                $this->indexConfigs[$indexName]['config']['mappings'][$name]['_routing'] = $type['_routing'];
            }
            if (isset($type['mappings'])) {
                $this->indexConfigs[$indexName]['config']['mappings'][$name]['properties'] = $type['mappings'];
                $typeName = sprintf('%s/%s', $indexName, $name);
                $this->typeFields[$typeName] = $type['mappings'];
            }
            if (isset($type['_parent'])) {
                $this->indexConfigs[$indexName]['config']['mappings'][$name]['_parent'] = array('type' => $type['_parent']['type']);
                $typeName = sprintf('%s/%s', $indexName, $name);
                $this->typeFields[$typeName]['_parent'] = $type['_parent'];
            }
            if (isset($type['persistence'])) {
                $this->loadTypePersistenceIntegration($type['persistence'], $container, $typeDef, $indexName, $name);
            }
            if (isset($type['index_analyzer'])) {
                $this->indexConfigs[$indexName]['config']['mappings'][$name]['index_analyzer'] = $type['index_analyzer'];
            }
            if (isset($type['search_analyzer'])) {
                $this->indexConfigs[$indexName]['config']['mappings'][$name]['search_analyzer'] = $type['search_analyzer'];
            }
            if (isset($type['index'])) {
                $this->indexConfigs[$indexName]['config']['mappings'][$name]['index'] = $type['index'];
            }
        }
    }

    /**
     * Merges two arrays without reindexing numeric keys.
     *
     * @param array $array1 An array to merge
     * @param array $array2 An array to merge
     *
     * @return array The merged array
     */
    static protected function deepArrayUnion($array1, $array2)
    {
        foreach ($array2 as $key => $value) {
            if (is_array($value) && isset($array1[$key]) && is_array($array1[$key])) {
                $array1[$key] = self::deepArrayUnion($array1[$key], $value);
            } else {
                $array1[$key] = $value;
            }
        }

        return $array1;
    }

    /**
     * Loads the optional provider and finder for a type
     *
     * @param array $typeConfig
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param \Symfony\Component\DependencyInjection\Definition $typeDef
     * @param $indexName
     * @param $typeName
     */
    protected function loadTypePersistenceIntegration(array $typeConfig, ContainerBuilder $container, Definition $typeDef, $indexName, $typeName)
    {
        $this->loadDriver($container, $typeConfig['driver']);

        $elasticaToModelTransformerId = $this->loadElasticaToModelTransformer($typeConfig, $container, $indexName, $typeName);
        $modelToElasticaTransformerId = $this->loadModelToElasticaTransformer($typeConfig, $container, $indexName, $typeName);
        $objectPersisterId            = $this->loadObjectPersister($typeConfig, $typeDef, $container, $indexName, $typeName, $modelToElasticaTransformerId);

        if (isset($typeConfig['provider'])) {
            $this->loadTypeProvider($typeConfig, $container, $objectPersisterId, $typeDef, $indexName, $typeName);
        }
        if (isset($typeConfig['finder'])) {
            $this->loadTypeFinder($typeConfig, $container, $elasticaToModelTransformerId, $typeDef, $indexName, $typeName);
        }
        if (isset($typeConfig['listener'])) {
            $this->loadTypeListener($typeConfig, $container, $objectPersisterId, $typeDef, $indexName, $typeName);
        }
    }

    protected function loadElasticaToModelTransformer(array $typeConfig, ContainerBuilder $container, $indexName, $typeName)
    {
        if (isset($typeConfig['elastica_to_model_transformer']['service'])) {
            return $typeConfig['elastica_to_model_transformer']['service'];
        }
        $abstractId = sprintf('fos_elastica.elastica_to_model_transformer.prototype.%s', $typeConfig['driver']);
        $serviceId = sprintf('fos_elastica.elastica_to_model_transformer.%s.%s', $indexName, $typeName);
        $serviceDef = new DefinitionDecorator($abstractId);
        $serviceDef->addTag('fos_elastica.elastica_to_model_transformer', array('type' => $typeName, 'index' => $indexName));

        // Doctrine has a mandatory service as first argument
        $argPos = ('propel' === $typeConfig['driver']) ? 0 : 1;

        $serviceDef->replaceArgument($argPos, $typeConfig['model']);
        $serviceDef->replaceArgument($argPos + 1, array(
            'hydrate'        => $typeConfig['elastica_to_model_transformer']['hydrate'],
            'identifier'     => $typeConfig['identifier'],
            'ignore_missing' => $typeConfig['elastica_to_model_transformer']['ignore_missing']
        ));
        $container->setDefinition($serviceId, $serviceDef);

        return $serviceId;
    }

    protected function loadModelToElasticaTransformer(array $typeConfig, ContainerBuilder $container, $indexName, $typeName)
    {
        if (isset($typeConfig['model_to_elastica_transformer']['service'])) {
            return $typeConfig['model_to_elastica_transformer']['service'];
        }
        $abstractId = sprintf('fos_elastica.model_to_elastica_transformer.prototype.auto');
        $serviceId = sprintf('fos_elastica.model_to_elastica_transformer.%s.%s', $indexName, $typeName);
        $serviceDef = new DefinitionDecorator($abstractId);
        $serviceDef->replaceArgument(0, array(
            'identifier' => $typeConfig['identifier']
        ));
        $container->setDefinition($serviceId, $serviceDef);

        return $serviceId;
    }

    protected function loadObjectPersister(array $typeConfig, Definition $typeDef, ContainerBuilder $container, $indexName, $typeName, $transformerId)
    {
        $abstractId = sprintf('fos_elastica.object_persister.prototype');
        $serviceId = sprintf('fos_elastica.object_persister.%s.%s', $indexName, $typeName);
        $serviceDef = new DefinitionDecorator($abstractId);
        $serviceDef->replaceArgument(0, $typeDef);
        $serviceDef->replaceArgument(1, new Reference($transformerId));
        $serviceDef->replaceArgument(2, $typeConfig['model']);
        $serviceDef->replaceArgument(3, $this->typeFields[sprintf('%s/%s', $indexName, $typeName)]);
        $container->setDefinition($serviceId, $serviceDef);

        return $serviceId;
    }

    protected function loadTypeProvider(array $typeConfig, ContainerBuilder $container, $objectPersisterId, $typeDef, $indexName, $typeName)
    {
        if (isset($typeConfig['provider']['service'])) {
            return $typeConfig['provider']['service'];
        }

        $providerId = sprintf('fos_elastica.provider.%s.%s', $indexName, $typeName);
        $providerDef = new DefinitionDecorator('fos_elastica.provider.prototype.' . $typeConfig['driver']);
        $providerDef->addTag('fos_elastica.provider', array('index' => $indexName, 'type' => $typeName));
        $providerDef->replaceArgument(0, new Reference($objectPersisterId));
        $providerDef->replaceArgument(1, $typeConfig['model']);
        // Propel provider can simply ignore Doctrine-specific options
        $providerDef->replaceArgument(2, array_diff_key($typeConfig['provider'], array('service' => 1)));
        $container->setDefinition($providerId, $providerDef);

        return $providerId;
    }

    protected function loadTypeListener(array $typeConfig, ContainerBuilder $container, $objectPersisterId, $typeDef, $indexName, $typeName)
    {
        if (isset($typeConfig['listener']['service'])) {
            return $typeConfig['listener']['service'];
        }
        $abstractListenerId = sprintf('fos_elastica.listener.prototype.%s', $typeConfig['driver']);
        $listenerId = sprintf('fos_elastica.listener.%s.%s', $indexName, $typeName);
        $listenerDef = new DefinitionDecorator($abstractListenerId);
        $listenerDef->replaceArgument(0, new Reference($objectPersisterId));
        $listenerDef->replaceArgument(1, $typeConfig['model']);
        $listenerDef->replaceArgument(3, $typeConfig['identifier']);
        $listenerDef->replaceArgument(2, $this->getDoctrineEvents($typeConfig));
        switch ($typeConfig['driver']) {
            case 'orm': $listenerDef->addTag('doctrine.event_subscriber'); break;
            case 'mongodb': $listenerDef->addTag('doctrine_mongodb.odm.event_subscriber'); break;
        }
        if (isset($typeConfig['listener']['is_indexable_callback'])) {
            $callback = $typeConfig['listener']['is_indexable_callback'];

            if (is_array($callback)) {
                list($class) = $callback + array(null);
                if (is_string($class) && !class_exists($class)) {
                    $callback[0] = new Reference($class);
                }
            }

            $listenerDef->addMethodCall('setIsIndexableCallback', array($callback));
        }
        $container->setDefinition($listenerId, $listenerDef);

        return $listenerId;
    }

    private function getDoctrineEvents(array $typeConfig)
    {
        $events = array();
        $eventMapping = array(
            'insert' => array('postPersist'),
            'update' => array('postUpdate'),
            'delete' => array('postRemove', 'preRemove')
        );

        foreach ($eventMapping as $event => $doctrineEvents) {
            if (isset($typeConfig['listener'][$event]) && $typeConfig['listener'][$event]) {
                $events = array_merge($events, $doctrineEvents);
            }
        }

        return $events;
    }

    protected function loadTypeFinder(array $typeConfig, ContainerBuilder $container, $elasticaToModelId, $typeDef, $indexName, $typeName)
    {
        if (isset($typeConfig['finder']['service'])) {
            $finderId = $typeConfig['finder']['service'];
        } else {
            $abstractFinderId = 'fos_elastica.finder.prototype';
            $finderId = sprintf('fos_elastica.finder.%s.%s', $indexName, $typeName);
            $finderDef = new DefinitionDecorator($abstractFinderId);
            $finderDef->replaceArgument(0, $typeDef);
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
     * Loads the index manager
     *
     * @param array            $indexRefsByName
     * @param ContainerBuilder $container
     **/
    protected function loadIndexManager(array $indexRefsByName, ContainerBuilder $container)
    {
        $managerDef = $container->getDefinition('fos_elastica.index_manager');
        $managerDef->replaceArgument(0, $indexRefsByName);
        $managerDef->replaceArgument(1, new Reference('fos_elastica.index'));
    }

    /**
     * Loads the resetter
     *
     * @param array $indexConfigs
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    protected function loadResetter(array $indexConfigs, ContainerBuilder $container)
    {
        $resetterDef = $container->getDefinition('fos_elastica.resetter');
        $resetterDef->replaceArgument(0, $indexConfigs);
    }

    protected function loadDriver(ContainerBuilder $container, $driver)
    {
        if (in_array($driver, $this->loadedDrivers)) {
            return;
        }
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load($driver.'.xml');
        $this->loadedDrivers[] = $driver;
    }

    protected function createDefaultManagerAlias($defaultManager, ContainerBuilder $container)
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
}
