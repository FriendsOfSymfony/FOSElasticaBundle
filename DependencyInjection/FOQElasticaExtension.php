<?php

namespace FOQ\ElasticaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\FileLocator;
use InvalidArgumentException;

class FOQElasticaExtension extends Extension
{
	protected $supportedProviderDrivers = array('mongodb');
	protected $typeMappings = array();
	protected $loadedDoctrineDrivers = array();

	public function load(array $configs, ContainerBuilder $container)
	{
		$configuration = new Configuration();
		$processor = new Processor();
		$config = $processor->process($configuration->getConfigTree(), $configs);

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
		$indexIdsByName = $this->loadIndexes($config['indexes'], $container, $clientIdsByName, $config['default_client']);
		$indexDefsByName = array_map(function($id) use ($container) {
			return $container->getDefinition($id);
		}, $indexIdsByName);

		$this->loadIndexManager($indexDefsByName, $container->getDefinition($indexIdsByName[$config['default_index']]), $container);
		$this->loadMappingSetter($this->typeMappings, $container);

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
			$this->loadTypes(isset($index['types']) ? $index['types'] : array(), $container, $name, $indexId);
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
	protected function loadTypes(array $types, ContainerBuilder $container, $indexName, $indexId)
	{
		foreach ($types as $name => $type) {
			$typeId = sprintf('%s.%s', $indexId, $name);
			$typeDefArgs = array($name);
			$typeDef = new Definition('%foq_elastica.type.class%', $typeDefArgs);
			$typeDef->setFactoryService($indexId);
			$typeDef->setFactoryMethod('getType');
			$container->setDefinition($typeId, $typeDef);
			if (isset($type['mappings'])) {
				$this->typeMappings[] = array(
					new Reference($typeId),
					$type['mappings']
				);
			}
			if (isset($type['doctrine'])) {
				$this->loadTypeDoctrineIntegration($type['doctrine'], $container, $typeDef, $indexName, $name);
			}
		}
	}

	/**
	 * Loads the optional provider and finder for a type
	 *
	 * @return null
	 **/
	public function loadTypeDoctrineIntegration(array $config, ContainerBuilder $container, Definition $typeDef, $indexName, $typeName)
	{
		if (!in_array($config['driver'], $this->supportedProviderDrivers)) {
			throw new InvalidArgumentException(sprintf('The provider driver "%s" is not supported'));
		}
		$this->loadDoctrineDriver($container, $config['driver']);
		if (isset($config['provider'])) {
			$abstractProviderId = sprintf('foq_elastica.provider.prototype.%s', $config['driver']);
			$providerId = sprintf('foq_elastica.provider.%s.%s', $indexName, $typeName);
			$providerDef = new DefinitionDecorator($abstractProviderId);
			$providerDef->setArgument(0, $typeDef);
			$providerDef->setArgument(3, $config['model']);
			$providerDef->setArgument(4, array_merge($config['provider'], array(
				'identifier' => $config['identifier']
			)));
			$container->setDefinition($providerId, $providerDef);
			$container->getDefinition('foq_elastica.populator')
				->addMethodCall('addProvider', array($providerId, new Reference($providerId)));
		}
		if (isset($config['finder'])) {
			$abstractMapperId = sprintf('foq_elastica.mapper.prototype.%s', $config['driver']);
			$mapperId = sprintf('foq_elastica.mapper.%s.%s', $indexName, $typeName);
			$mapperDef = new DefinitionDecorator($abstractMapperId);
			$mapperDef->setArgument(1, $config['model']);
			$mapperDef->setArgument(2, array_merge($config['finder'], array(
				'identifier' => $config['identifier']
			)));
			$container->setDefinition($mapperId, $mapperDef);
			$abstractFinderId = 'foq_elastica.finder.prototype';
			$finderId = sprintf('foq_elastica.finder.%s.%s', $indexName, $typeName);
			$finderDef = new DefinitionDecorator($abstractFinderId);
			$finderDef->setArgument(0, $typeDef);
			$finderDef->setArgument(1, new Reference($mapperId));
			$container->setDefinition($finderId, $finderDef);
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

	/**
	 * Loads the mapping setter
	 *
	 * @return null
	 **/
	public function loadMappingSetter(array $mappings, ContainerBuilder $container)
	{
		$managerDef = $container->getDefinition('foq_elastica.mapping_setter');
		$managerDef->setArgument(0, $mappings);
	}

	protected function loadDoctrineDriver(ContainerBuilder $container, $driver)
	{
		if (in_array($driver, $this->loadedDoctrineDrivers)) {
			return;
		}
		$loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
		$loader->load($driver.'.xml');
		$this->loadedDoctrineDrivers[] = $driver;
	}
}
