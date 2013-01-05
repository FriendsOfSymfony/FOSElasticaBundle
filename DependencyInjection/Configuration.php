<?php

namespace FOQ\ElasticaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    private $supportedDrivers = array('orm', 'mongodb', 'propel');

    /**
     * Generates the configuration tree.
     *
     * @return \Symfony\Component\DependencyInjection\Configuration\NodeInterface
     */
    public function getConfigTree()
    {
        return $this->getConfigTreeBuilder()->buildTree();
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('foq_elastica', 'array');

        $this->addClientsSection($rootNode);
        $this->addIndexesSection($rootNode);

        $rootNode
            ->children()
                ->scalarNode('default_client')->end()
                ->scalarNode('default_index')->end()
                ->scalarNode('default_manager')->defaultValue('orm')->end()
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * Adds the configuration for the "clients" key
     */
    private function addClientsSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->fixXmlConfig('client')
            ->children()
                ->arrayNode('clients')
                    ->useAttributeAsKey('id')
                    ->prototype('array')
                        ->performNoDeepMerging()
                        ->beforeNormalization()
                            ->ifTrue(function($v) { return isset($v['host']) && isset($v['port']); })
                            ->then(function($v) {
                                return array(
                                    'servers' => array(
                                        array(
                                            'host' => $v['host'],
                                            'port' => $v['port'],
                                        )
                                    )
                                );
                            })
                        ->end()
                        ->beforeNormalization()
                            ->ifTrue(function($v) { return isset($v['url']); })
                            ->then(function($v) {
                                return array(
                                    'servers' => array(
                                        array(
                                            'url' => $v['url'],
                                        )
                                    )
                                );
                            })
                        ->end()
                        ->children()
                            ->arrayNode('servers')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('url')->end()
                                        ->scalarNode('host')->end()
                                        ->scalarNode('port')->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->scalarNode('timeout')->end()
                            ->scalarNode('headers')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * Adds the configuration for the "indexes" key
     */
    private function addIndexesSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->fixXmlConfig('index')
            ->children()
                ->arrayNode('indexes')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->performNoDeepMerging()
                        ->children()
                            ->scalarNode('client')->end()
                            ->scalarNode('finder')
                                ->treatNullLike(true)
                                ->defaultFalse()
                            ->end()
                            ->arrayNode('type_prototype')
                                ->children()
                                    ->scalarNode('index_analyzer')->end()
                                    ->scalarNode('search_analyzer')->end()
                                    ->arrayNode('persistence')
                                        ->validate()
                                            ->ifTrue(function($v) { return isset($v['driver']) && 'propel' === $v['driver'] && isset($v['listener']); })
                                            ->thenInvalid('Propel doesn\'t support listeners')
                                            ->ifTrue(function($v) { return isset($v['driver']) && 'propel' === $v['driver'] && isset($v['repository']); })
                                            ->thenInvalid('Propel doesn\'t support the "repository" parameter')
                                        ->end()
                                        ->children()
                                            ->scalarNode('driver')
                                                ->validate()
                                                    ->ifNotInArray($this->supportedDrivers)
                                                    ->thenInvalid('The driver %s is not supported. Please choose one of '.json_encode($this->supportedDrivers))
                                                ->end()
                                            ->end()
                                            ->scalarNode('identifier')->defaultValue('id')->end()
                                            ->arrayNode('provider')
                                                ->children()
                                                    ->scalarNode('query_builder_method')->defaultValue('createQueryBuilder')->end()
                                                    ->scalarNode('batch_size')->defaultValue(100)->end()
                                                    ->scalarNode('clear_object_manager')->defaultTrue()->end()
                                                    ->scalarNode('service')->end()
                                                ->end()
                                            ->end()
                                            ->arrayNode('listener')
                                                ->children()
                                                    ->scalarNode('insert')->defaultTrue()->end()
                                                    ->scalarNode('update')->defaultTrue()->end()
                                                    ->scalarNode('delete')->defaultTrue()->end()
                                                    ->scalarNode('service')->end()
                                                    ->variableNode('is_indexable_callback')->defaultNull()->end()
                                                ->end()
                                            ->end()
                                            ->arrayNode('finder')
                                                ->children()
                                                    ->scalarNode('service')->end()
                                                ->end()
                                            ->end()
                                            ->arrayNode('elastica_to_model_transformer')
                                                ->addDefaultsIfNotSet()
                                                ->children()
                                                    ->scalarNode('hydrate')->defaultTrue()->end()
                                                    ->scalarNode('service')->end()
                                                ->end()
                                            ->end()
                                            ->arrayNode('model_to_elastica_transformer')
                                                ->addDefaultsIfNotSet()
                                                ->children()
                                                    ->scalarNode('service')->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->variableNode('settings')->treatNullLike(array())->end()
                        ->end()
                        ->append($this->getTypesNode())
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * Returns the array node used for "types".
     */
    protected function getTypesNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('types');

        $node
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->treatNullLike(array())
                ->children()
                    ->scalarNode('index_analyzer')->end()
                    ->scalarNode('search_analyzer')->end()
                    ->arrayNode('persistence')
                        ->validate()
                            ->ifTrue(function($v) { return isset($v['driver']) && 'propel' === $v['driver'] && isset($v['listener']); })
                            ->thenInvalid('Propel doesn\'t support listeners')
                            ->ifTrue(function($v) { return isset($v['driver']) && 'propel' === $v['driver'] && isset($v['repository']); })
                            ->thenInvalid('Propel doesn\'t support the "repository" parameter')
                        ->end()
                        ->children()
                            ->scalarNode('driver')
                                ->validate()
                                    ->ifNotInArray($this->supportedDrivers)
                                    ->thenInvalid('The driver %s is not supported. Please choose one of '.json_encode($this->supportedDrivers))
                                ->end()
                            ->end()
                            ->scalarNode('model')->end()
                            ->scalarNode('repository')->end()
                            ->scalarNode('identifier')->defaultValue('id')->end()
                            ->arrayNode('provider')
                                ->children()
                                    ->scalarNode('query_builder_method')->defaultValue('createQueryBuilder')->end()
                                    ->scalarNode('batch_size')->defaultValue(100)->end()
                                    ->scalarNode('clear_object_manager')->defaultTrue()->end()
                                    ->scalarNode('service')->end()
                                ->end()
                            ->end()
                            ->arrayNode('listener')
                                ->children()
                                    ->scalarNode('insert')->defaultTrue()->end()
                                    ->scalarNode('update')->defaultTrue()->end()
                                    ->scalarNode('delete')->defaultTrue()->end()
                                    ->scalarNode('service')->end()
                                    ->variableNode('is_indexable_callback')->defaultNull()->end()
                                ->end()
                            ->end()
                            ->arrayNode('finder')
                                ->children()
                                    ->scalarNode('service')->end()
                                ->end()
                            ->end()
                            ->arrayNode('elastica_to_model_transformer')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('hydrate')->defaultTrue()->end()
                                    ->scalarNode('service')->end()
                                ->end()
                            ->end()
                            ->arrayNode('model_to_elastica_transformer')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('service')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->append($this->getMappingsNode())
                ->append($this->getSourceNode())
                ->append($this->getBoostNode())
            ->end()
        ;

        return $node;
    }

    /**
     * Returns the array node used for "mappings".
     */
    protected function getMappingsNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('mappings');

        $node
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->treatNullLike(array())
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('type')->defaultValue('string')->end()
                    ->scalarNode('boost')->end()
                    ->scalarNode('store')->end()
                    ->scalarNode('index')->end()
                    ->scalarNode('index_analyzer')->end()
                    ->scalarNode('search_analyzer')->end()
                    ->scalarNode('analyzer')->end()
                    ->scalarNode('term_vector')->end()
                    ->scalarNode('null_value')->end()
                    ->booleanNode('include_in_all')->defaultValue('true')->end()
                    ->scalarNode('lat_lon')->end()
                    ->arrayNode('fields')
                        ->useAttributeAsKey('name')
                        ->prototype('array')
                            ->treatNullLike(array())
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('type')->defaultValue('string')->end()
                                ->scalarNode('boost')->end()
                                ->scalarNode('store')->end()
                                ->scalarNode('index')->end()
                                ->scalarNode('index_analyzer')->end()
                                ->scalarNode('search_analyzer')->end()
                                ->scalarNode('analyzer')->end()
                                ->scalarNode('term_vector')->end()
                                ->scalarNode('null_value')->end()
                                ->booleanNode('include_in_all')->defaultValue('true')->end()
                                ->scalarNode('lat_lon')->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('_parent')
                        ->treatNullLike(array())
                        ->children()
                            ->scalarNode('type')->end()
                            ->scalarNode('identifier')->defaultValue('id')->end()
                        ->end()
                    ->end()
                    ->arrayNode('properties')
                        ->useAttributeAsKey('name')
                        ->prototype('array')
                            ->treatNullLike(array())
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('type')->defaultValue('string')->end()
                                ->scalarNode('boost')->end()
                                ->scalarNode('store')->end()
                                ->scalarNode('index')->end()
                                ->scalarNode('index_analyzer')->end()
                                ->scalarNode('search_analyzer')->end()
                                ->scalarNode('analyzer')->end()
                                ->scalarNode('term_vector')->end()
                                ->scalarNode('null_value')->end()
                                ->booleanNode('include_in_all')->defaultValue('true')->end()
                                ->scalarNode('lat_lon')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    /**
     * Returns the array node used for "_source".
     */
    protected function getSourceNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('_source');

        $node
            ->children()
                ->arrayNode('excludes')
                    ->useAttributeAsKey('name')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('includes')
                    ->useAttributeAsKey('name')
                    ->prototype('scalar')->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    /**
     * Returns the array node used for "_boost".
     */
    protected function getBoostNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('_boost');

        $node
            ->children()
                ->scalarNode('name')->end()
                ->scalarNode('null_value')->end()
            ->end()
        ;

        return $node;
    }
}
