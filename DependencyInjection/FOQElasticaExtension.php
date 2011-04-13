<?php

namespace FOQ\ElasticaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\FileLocator;
use InvalidArgumentException;

class FOQElasticaExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('config.xml');

        $configuration = new Configuration();
        $processor = new Processor();

        $config = $processor->process($configuration->getConfigTree(), $configs);

        if (empty ($config['default_client'])) {
            $keys = array_keys($config['clients']);
            $config['default_client'] = reset($keys);
        }

        if (empty ($config['default_index'])) {
            $keys = array_keys($config['indexes']);
            $config['default_index'] = reset($keys);
        }

        $clientIdsByName = $this->loadClients($config['clients'], $container);
        $indexIdsByName = $this->loadIndexes($config['indexes'], $container, $clientIdsByName, $config['default_client']);
        $indexDefsByName = array_map(function($id) use ($container) {
            return $container->getDefinition($id);
        }, $indexIdsByName);

        $this->loadIndexManager($indexDefsByName, $container->getDefinition($indexIdsByName[$config['default_index']]), $container);

        $container->setAlias('foq_elastica.client', sprintf('foq_elastica.client.%s', $config['default_client']));
        $container->setAlias('foq_elastica.index', sprintf('foq_elastica.index.%s', $config['default_index']));
    }

    /**
     * Loads the configured clients.
     *
     * @param array $config An array of clients configurations
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    protected function loadClients(array $clients, ContainerBuilder $container)
    {
        $clientIds = array();
        foreach ($clients as $name => $client) {
            $clientDefArgs = array(
                isset($client['host']) ? $client['host'] : null,
                isset($client['port']) ? $client['port'] : array(),
            );
            $clientDef = new Definition('%foq_elastica.client.class%', $clientDefArgs);
            $clientId = sprintf('foq_elastica.client.%s', $name);
            $container->setDefinition($clientId, $clientDef);
            $clientIds[$name] = $clientId;
        }

        return $clientIds;
    }

    /**
     * Loads the configured indexes.
     *
     * @param array $config An array of indexes configurations
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    protected function loadIndexes(array $indexes, ContainerBuilder $container, array $clientIdsByName, $defaultClientName)
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
            $clientDef = $container->getDefinition($clientId);
            $indexId = sprintf('foq_elastica.index.%s', $name);
            $indexDefArgs = array($name);
            $indexDef = new Definition('%foq_elastica.index.class%', $indexDefArgs);
            $indexDef->setFactoryService($clientId);
            $indexDef->setFactoryMethod('getIndex');
            $container->setDefinition($indexId, $indexDef);
            $this->loadTypes(isset($index['types']) ? $index['types'] : array(), $container, $indexId);
            $indexIds[$name] = $indexId;
        }

        return $indexIds;
    }

    /**
     * Loads the configured types.
     *
     * @param array $config An array of types configurations
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    protected function loadTypes(array $types, ContainerBuilder $container, $indexId)
    {
        foreach ($types as $name => $type) {
            $typeDefArgs = array($name);
            $typeDef = new Definition('%foq_elastica.type.class%', $typeDefArgs);
            $typeDef->setFactoryService($indexId);
            $typeDef->setFactoryMethod('getType');
            $container->setDefinition(sprintf('%s.%s', $indexId, $name), $typeDef);
        }
    }

    /**
     * Loads the index manager
     *
     * @return null
     **/
    public function loadIndexManager(array $indexDefs, $defaultIndexId, ContainerBuilder $container)
    {
        $managerDef = $container->getDefinition('foq_elastica.index_manager');
        $managerDef->setArgument(0, $indexDefs);
        $managerDef->setArgument(1, new Reference('foq_elastica.index'));
    }
}
