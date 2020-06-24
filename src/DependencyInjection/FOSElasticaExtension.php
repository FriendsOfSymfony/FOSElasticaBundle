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

use Elastica\Client as ElasticaClient;
use FOS\ElasticaBundle\Elastica\Client;
use FOS\ElasticaBundle\Manager\RepositoryManagerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
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
     * An array of index templates as configured by the extension.
     *
     * @var array
     */
    private $indexTemplateConfigs = array();

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
        $container->setAlias(ElasticaClient::class, new Alias('fos_elastica.client', false));
        $container->setAlias(Client::class, 'fos_elastica.client');
        $container->getAlias(Client::class)->setPublic(false);

        $this->loadIndexes($config['indexes'], $container);
        $container->setAlias('fos_elastica.index', sprintf('fos_elastica.index.%s', $config['default_index']));
        $container->getAlias('fos_elastica.index')->setPublic(true);
        $container->setParameter('fos_elastica.default_index', $config['default_index']);

        if ($usedIndexNames = \array_intersect_key($config['indexes'], $config['index_templates'])) {
            throw new \DomainException(
                \sprintf(
                    'Index names "%s" are already in use and can not be used for index templates names',
                    \implode('","', \array_keys($usedIndexNames))
                )
            );
        }
        $this->loadIndexTemplates($config['index_templates'], $container);

        $container->getDefinition('fos_elastica.config_source.container')->replaceArgument(0, $this->indexConfigs);
        $container
            ->getDefinition('fos_elastica.config_source.template_container')
            ->replaceArgument(0, $this->indexTemplateConfigs);

        $this->loadIndexManager($container);
        $this->loadIndexTemplateManager($container);

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
            $clientDef->replaceArgument(1, null);

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
                'model' => $index['persistence']['model'] ?? null,
                'name' => $name,
                'settings' => $index['settings'],
                'type_prototype' => isset($index['type_prototype']) ? $index['type_prototype'] : [],
                'use_alias' => $index['use_alias'],
            ];

            if ($index['finder']) {
                $this->loadIndexFinder($container, $name, $reference);
            }

            $this->loadTypes((array) $index['types'], $this->indexConfigs[$name]);

            if (isset($index['indexable_callback'])) {
                $indexableCallbacks[$name] = $this->buildCallback($index['indexable_callback'], $name);
            }

            $this->loadIndexSerializerIntegration($index['serializer'] ?? [], $container, $reference);

            if (isset($index['persistence'])) {
                $this->loadIndexPersistenceIntegration($index['persistence'], $container, $reference, $name);
            }
        }

        $indexable = $container->getDefinition('fos_elastica.indexable');
        $indexable->replaceArgument(0, $indexableCallbacks);
    }

    /**
     * Loads the configured indexes.
     *
     * @param array            $indexTemplates   An array of indexes configurations
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    private function loadIndexTemplates(array $indexTemplates, ContainerBuilder $container)
    {
        foreach ($indexTemplates as $name => $indexTemplate) {
            $indexId = sprintf('fos_elastica.index_template.%s', $name);
            $indexTemplateName = isset($indexTemplate['template_name']) ? $indexTemplate['template_name'] : $name;

            $indexDef = new ChildDefinition('fos_elastica.index_template_prototype');
            $indexDef->setFactory([new Reference('fos_elastica.client'), 'getIndexTemplate']);
            $indexDef->replaceArgument(0, $indexTemplateName);
            $indexDef->addTag('fos_elastica.index_template', array(
                'name' => $name,
            ));

            if (isset($indexTemplate['client'])) {
                $client = $this->getClient($indexTemplate['client']);
                $indexDef->setFactory([$client, 'getIndexTemplate']);
            }

            $container->setDefinition($indexId, $indexDef);
            $reference = new Reference($indexId);

            $this->indexTemplateConfigs[$name] = array(
                'elasticsearch_name' => $indexTemplateName,
                'reference' => $reference,
                'name' => $name,
                'settings' => $indexTemplate['settings'],
                'template' => $indexTemplate['template'],
            );

            $this->loadTypes((array) $indexTemplate['types'], $this->indexTemplateConfigs[$name]);
        }
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
    private function loadIndexFinder(ContainerBuilder $container, string $name, Reference $index): void
    {
        $finderId = sprintf('fos_elastica.finder.%s', $name);
        $finderDef = new ChildDefinition('fos_elastica.finder');
        $finderDef->replaceArgument(0, $index);
        $finderDef->replaceArgument(1, new Reference(sprintf('fos_elastica.elastica_to_model_transformer.%s', $name)));

        $container->setDefinition($finderId, $finderDef);
    }

    /**
     * Loads the configured types.
     */
    private function loadTypes(array $types, array &$indexConfig): void
    {
        foreach ($types as $name => $type) {
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
                '_routing',
                '_source',
            ] as $field) {
                if (isset($type[$field])) {
                    $typeConfig['mapping'][$field] = $type[$field];
                }
            }

            foreach ([
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

            $indexConfig['types'][$name] = $typeConfig;
        }
    }

    private function buildCallback($indexCallback, $indexName)
    {
        if (is_array($indexCallback)) {
            if (!isset($indexCallback[0])) {
                throw new \InvalidArgumentException(sprintf('Invalid indexable_callback for index %s', $indexName));
            }

            $classOrServiceRef = $this->transformServiceReference($indexCallback[0]);
            if ($classOrServiceRef instanceof Reference && !isset($indexCallback[1])) {
                return $classOrServiceRef; // __invoke
            }

            if (!isset($indexCallback[1])) {
                throw new \InvalidArgumentException(sprintf('Invalid indexable_callback for index %s', $indexName));
            }

            return [$classOrServiceRef, $indexCallback[1]];
        }

        if (is_string($indexCallback)) {
            return $this->transformServiceReference($indexCallback);
        }

        throw new \InvalidArgumentException(sprintf('Invalid indexable_callback for index %s', $indexName));
    }

    private function transformServiceReference($classOrService)
    {
        return 0 === strpos($classOrService, '@') ? new Reference(substr($classOrService, 1)) : $classOrService;
    }

    private function loadIndexSerializerIntegration(array $config, ContainerBuilder $container, Reference $indexRef): void
    {
        if ($container->hasDefinition('fos_elastica.serializer_callback_prototype')) {
            $indexSerializerId = sprintf('%s.serializer.callback', $indexRef);
            $indexSerializerDef = new ChildDefinition('fos_elastica.serializer_callback_prototype');

            if (isset($config['groups'])) {
                $indexSerializerDef->addMethodCall('setGroups', [$config['groups']]);
            }

            if (isset($config['serialize_null'])) {
                $indexSerializerDef->addMethodCall('setSerializeNull', [$config['serialize_null']]);
            }

            if (isset($config['version'])) {
                $indexSerializerDef->addMethodCall('setVersion', [$config['version']]);
            }

            $container->setDefinition($indexSerializerId, $indexSerializerDef);
        }
    }

    /**
     * Loads the optional provider and finder for a type.
     */
    private function loadIndexPersistenceIntegration(array $config, ContainerBuilder $container, Reference $indexRef, string $indexName): void
    {
        if (isset($config['driver'])) {
            $this->loadDriver($container, $config['driver']);
        }

        $elasticaToModelTransformerId = $this->loadElasticaToModelTransformer($config, $container, $indexName);
        $modelToElasticaTransformerId = $this->loadModelToElasticaTransformer($config, $container, $indexName);
        $objectPersisterId = $this->loadObjectPersister($config, $indexRef, $container, $indexName, $modelToElasticaTransformerId);

        if (isset($config['provider'])) {
            $this->loadTypePagerProvider($config, $container, $indexName);
        }
        if (isset($config['finder'])) {
            $this->loadTypeFinder($config, $container, $elasticaToModelTransformerId, $indexRef, $indexName);
        }
        if (isset($config['listener']) && $config['listener']['enabled']) {
            $this->loadTypeListener($config, $container, $objectPersisterId, $indexName);
        }
    }

    /**
     * Creates and loads an ElasticaToModelTransformer.
     */
    private function loadElasticaToModelTransformer(array $persistenceConfig, ContainerBuilder $container, string $indexName): string
    {
        if (isset($persistenceConfig['elastica_to_model_transformer']['service'])) {
            return $persistenceConfig['elastica_to_model_transformer']['service'];
        }

        /* Note: transformer services may conflict with "prototype.driver", if
         * the index and type names were "prototype" and a driver, respectively.
         */
        $abstractId = sprintf('fos_elastica.elastica_to_model_transformer.prototype.%s', $persistenceConfig['driver']);
        $serviceId = sprintf('fos_elastica.elastica_to_model_transformer.%s', $indexName);
        $serviceDef = new ChildDefinition($abstractId);
        $serviceDef->addTag('fos_elastica.elastica_to_model_transformer', ['index' => $indexName]);

        $serviceDef->replaceArgument(1, $persistenceConfig['model']);
        $serviceDef->replaceArgument(2, array_merge($persistenceConfig['elastica_to_model_transformer'], [
            'identifier' => $persistenceConfig['identifier'],
        ]));
        $container->setDefinition($serviceId, $serviceDef);

        return $serviceId;
    }

    /**
     * Creates and loads a ModelToElasticaTransformer for an index/type.
     */
    private function loadModelToElasticaTransformer(array $typeConfig, ContainerBuilder $container, string $indexName): string
    {
        if (isset($typeConfig['model_to_elastica_transformer']['service'])) {
            return $typeConfig['model_to_elastica_transformer']['service'];
        }

        $abstractId = $container->hasDefinition('fos_elastica.serializer_callback_prototype') ?
            'fos_elastica.model_to_elastica_identifier_transformer' :
            'fos_elastica.model_to_elastica_transformer';

        $serviceId = sprintf('fos_elastica.model_to_elastica_transformer.%s', $indexName);
        $serviceDef = new ChildDefinition($abstractId);
        $serviceDef->replaceArgument(0, [
            'identifier' => $typeConfig['identifier'],
            'index' => $indexName,
        ]);
        $container->setDefinition($serviceId, $serviceDef);

        return $serviceId;
    }

    /**
     * Creates and loads an object persister for a type.
     */
    private function loadObjectPersister(array $typeConfig, Reference $indexRef, ContainerBuilder $container, string $indexName, string $transformerId): string
    {
        if (isset($typeConfig['persister']['service'])) {
            return $typeConfig['persister']['service'];
        }

        $arguments = [
            $indexRef,
            new Reference($transformerId),
            $typeConfig['model'],
        ];

        if ($container->hasDefinition('fos_elastica.serializer_callback_prototype')) {
            $abstractId = 'fos_elastica.object_serializer_persister';
            $callbackId = sprintf('%s.serializer.callback', $indexRef);
            $arguments[] = [new Reference($callbackId), 'serialize'];
        } else {
            $abstractId = 'fos_elastica.object_persister';
            $mapping = $this->indexConfigs[$indexName]['types']['_doc']['mapping'];
            $argument = $mapping['properties'];
            $arguments[] = $argument;
        }

        $arguments[] = array_intersect_key($typeConfig['persister'], array_flip(['refresh']));

        $serviceId = sprintf('fos_elastica.object_persister.%s', $indexName);
        $serviceDef = new ChildDefinition($abstractId);
        foreach ($arguments as $i => $argument) {
            $serviceDef->replaceArgument($i, $argument);
        }

        $serviceDef->addTag('fos_elastica.persister', ['index' => $indexName]);

        $container->setDefinition($serviceId, $serviceDef);

        return $serviceId;
    }

    /**
     * Loads a pager provider for a type.
     */
    private function loadTypePagerProvider(array $typeConfig, ContainerBuilder $container, string $indexName): string
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
            default:
                throw new \LogicException(sprintf('The pager provider for driver "%s" does not exist.', $driver));
        }

        /* Note: provider services may conflict with "prototype.driver", if the
         * index and type names were "prototype" and a driver, respectively.
         */
        $providerId = sprintf('fos_elastica.pager_provider.%s', $indexName);
        $providerDef->addTag('fos_elastica.pager_provider', ['index' => $indexName]);

        $container->setDefinition($providerId, $providerDef);

        return $providerId;
    }

    /**
     * Loads doctrine listeners to handle indexing of new or updated objects.
     */
    private function loadTypeListener(array $typeConfig, ContainerBuilder $container, string $objectPersisterId, string $indexName): string
    {
        if (isset($typeConfig['listener']['service'])) {
            return $typeConfig['listener']['service'];
        }

        /* Note: listener services may conflict with "prototype.driver", if the
         * index and type names were "prototype" and a driver, respectively.
         */
        $abstractListenerId = sprintf('fos_elastica.listener.prototype.%s', $typeConfig['driver']);
        $listenerId = sprintf('fos_elastica.listener.%s', $indexName);
        $listenerDef = new ChildDefinition($abstractListenerId);
        $listenerDef->replaceArgument(0, new Reference($objectPersisterId));
        $listenerDef->replaceArgument(3, $typeConfig['listener']['logger'] ?
            new Reference($typeConfig['listener']['logger']) :
            null
        );
        $listenerConfig = [
            'identifier' => $typeConfig['identifier'],
            'indexName' => $indexName,
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
     */
    private function loadTypeFinder(array $typeConfig, ContainerBuilder $container, string $elasticaToModelId, Reference $indexRef, string $indexName): string
    {
        if (isset($typeConfig['finder']['service'])) {
            $finderId = $typeConfig['finder']['service'];
        } else {
            $finderId = sprintf('fos_elastica.finder.%s', $indexName);
            $finderDef = new ChildDefinition('fos_elastica.finder');
            $finderDef->replaceArgument(0, $indexRef);
            $finderDef->replaceArgument(1, new Reference($elasticaToModelId));
            $container->setDefinition($finderId, $finderDef);
        }

        $arguments = [$indexName, new Reference($finderId)];
        if (isset($typeConfig['repository'])) {
            $arguments[] = $typeConfig['repository'];
        }

        $container->getDefinition('fos_elastica.repository_manager')
            ->addMethodCall('addType', $arguments);

        $managerId = sprintf('fos_elastica.manager.%s', $typeConfig['driver']);
        $container->getDefinition($managerId)
            ->addMethodCall('addEntity', [$typeConfig['model'], $indexName]);

        return $finderId;
    }

    /**
     * Loads the index manager.
     **/
    private function loadIndexManager(ContainerBuilder $container): void
    {
        $indexRefs = array_map(function ($index) {
            return $index['reference'];
        }, $this->indexConfigs);

        $managerDef = $container->getDefinition('fos_elastica.index_manager');
        $managerDef->replaceArgument(0, $indexRefs);
    }

    /**
     * Load index template manager
     */
    private function loadIndexTemplateManager(ContainerBuilder $container): void
    {
        $indexTemplateRefs = array_map(function ($index) {
            return $index['reference'];
        }, $this->indexTemplateConfigs);

        $managerDef = $container->getDefinition('fos_elastica.index_template_manager');
        $managerDef->replaceArgument(0, $indexTemplateRefs);
    }

    /**
     * Makes sure a specific driver has been loaded.
     */
    private function loadDriver(ContainerBuilder $container, string $driver): void
    {
        if (in_array($driver, $this->loadedDrivers, true)) {
            return;
        }

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load($driver.'.xml');
        $this->loadedDrivers[] = $driver;
    }

    /**
     * Loads and configures the serializer prototype.
     */
    private function loadSerializer(array $config, ContainerBuilder $container): void
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
     */
    private function createDefaultManagerAlias(string $defaultManager, ContainerBuilder $container): void
    {
        if (0 == count($this->loadedDrivers)) {
            return;
        }

        if (count($this->loadedDrivers) > 1
            && in_array($defaultManager, $this->loadedDrivers, true)
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
     * @throws \InvalidArgumentException
     */
    private function getClient(string $clientName): Reference
    {
        if (!array_key_exists($clientName, $this->clients)) {
            throw new \InvalidArgumentException(sprintf('The elastica client with name "%s" is not defined', $clientName));
        }

        return $this->clients[$clientName]['reference'];
    }
}
