<?php

namespace FOS\ElasticaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * Stores supported database drivers.
     *
     * @var array
     */
    private $supportedDrivers = array('orm', 'mongodb', 'propel');

    /**
     * If the kernel is running in debug mode.
     *
     * @var bool
     */
    private $debug;

    public function __construct($debug)
    {
        $this->debug = $debug;
    }

    /**
     * Generates the configuration tree.
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('fos_elastica', 'array');

        $this->addClientsSection($rootNode);
        $this->addIndexesSection($rootNode);

        $rootNode
            ->children()
                ->scalarNode('default_client')
                    ->info('Defaults to the first client defined')
                ->end()
                ->scalarNode('default_index')
                    ->info('Defaults to the first index defined')
                ->end()
                ->scalarNode('default_manager')->defaultValue('orm')->end()
                ->arrayNode('serializer')
                    ->treatNullLike(array())
                    ->children()
                        ->scalarNode('callback_class')->defaultValue('FOS\ElasticaBundle\Serializer\Callback')->end()
                        ->scalarNode('serializer')->defaultValue('serializer')->end()
                    ->end()
                ->end()
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
                        // BC - Renaming 'servers' node to 'connections'
                        ->beforeNormalization()
                        ->ifTrue(function($v) { return isset($v['servers']); })
                        ->then(function($v) {
                            $v['connections'] = $v['servers'];
                            unset($v['servers']);

                            return $v;
                        })
                        ->end()
                        // If there is no connections array key defined, assume a single connection.
                        ->beforeNormalization()
                        ->ifTrue(function ($v) { return is_array($v) && !array_key_exists('connections', $v); })
                        ->then(function ($v) {
                            return array(
                                'connections' => array($v)
                            );
                        })
                        ->end()
                        ->children()
                            ->arrayNode('connections')
                                ->requiresAtLeastOneElement()
                                ->prototype('array')
                                    ->fixXmlConfig('header')
                                    ->children()
                                        ->scalarNode('url')
                                            ->validate()
                                                ->ifTrue(function($url) { return $url && substr($url, -1) !== '/'; })
                                                ->then(function($url) { return $url.'/'; })
                                            ->end()
                                        ->end()
                                        ->scalarNode('host')->end()
                                        ->scalarNode('port')->end()
                                        ->scalarNode('proxy')->end()
                                        ->scalarNode('logger')
                                            ->defaultValue($this->debug ? 'fos_elastica.logger' : false)
                                            ->treatNullLike('fos_elastica.logger')
                                            ->treatTrueLike('fos_elastica.logger')
                                        ->end()
                                        ->arrayNode('headers')
                                            ->useAttributeAsKey('name')
                                            ->prototype('scalar')->end()
                                        ->end()
                                        ->scalarNode('transport')->end()
                                        ->scalarNode('timeout')->end()
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
                        ->children()
                            ->scalarNode('index_name')
                                ->info('Defaults to the name of the index, but can be modified if the index name is different in ElasticSearch')
                            ->end()
                            ->booleanNode('use_alias')->defaultValue(false)->end()
                            ->scalarNode('client')->end()
                            ->scalarNode('finder')
                                ->treatNullLike(true)
                                ->defaultFalse()
                            ->end()
                            ->arrayNode('type_prototype')
                                ->children()
                                    ->scalarNode('index_analyzer')->end()
                                    ->scalarNode('search_analyzer')->end()
                                    ->append($this->getPersistenceNode())
                                    ->append($this->getSerializerNode())
                                ->end()
                            ->end()
                            ->variableNode('settings')->defaultValue(array())->end()
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
                // BC - Renaming 'mappings' node to 'properties'
                ->beforeNormalization()
                ->ifTrue(function($v) { return array_key_exists('mappings', $v); })
                ->then(function($v) {
                    $v['properties'] = $v['mappings'];
                    unset($v['mappings']);

                    return $v;
                })
                ->end()
                // BC - Support the old is_indexable_callback property
                ->beforeNormalization()
                ->ifTrue(function ($v) {
                    return isset($v['persistence']) &&
                        isset($v['persistence']['listener']) &&
                        isset($v['persistence']['listener']['is_indexable_callback']);
                })
                ->then(function ($v) {
                    $v['indexable_callback'] = $v['persistence']['listener']['is_indexable_callback'];
                    unset($v['persistence']['listener']['is_indexable_callback']);

                    return $v;
                })
                ->end()
                // Support multiple dynamic_template formats to match the old bundle style
                // and the way ElasticSearch expects them
                ->beforeNormalization()
                ->ifTrue(function ($v) { return isset($v['dynamic_templates']); })
                ->then(function ($v) {
                    $dt = array();
                    foreach ($v['dynamic_templates'] as $key => $type) {
                        if (is_int($key)) {
                            $dt[] = $type;
                        } else {
                            $dt[][$key] = $type;
                        }
                    }

                    $v['dynamic_templates'] = $dt;

                    return $v;
                })
                ->end()
                ->children()
                    ->scalarNode('index_analyzer')->end()
                    ->scalarNode('search_analyzer')->end()
                    ->variableNode('indexable_callback')->end()
                    ->append($this->getPersistenceNode())
                    ->append($this->getSerializerNode())
                ->end()
                ->append($this->getIdNode())
                ->append($this->getPropertiesNode())
                ->append($this->getDynamicTemplateNode())
                ->append($this->getSourceNode())
                ->append($this->getBoostNode())
                ->append($this->getRoutingNode())
                ->append($this->getParentNode())
                ->append($this->getAllNode())
                ->append($this->getTimestampNode())
                ->append($this->getTtlNode())
            ->end()
        ;

        return $node;
    }

    /**
     * Returns the array node used for "properties".
     */
    protected function getPropertiesNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('properties');

        $node
            ->useAttributeAsKey('name')
            ->prototype('variable')
                ->treatNullLike(array());

        return $node;
    }

    /**
     * Returns the array node used for "dynamic_templates".
     */
    public function getDynamicTemplateNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('dynamic_templates');

        $node
            ->prototype('array')
                ->prototype('array')
                    ->children()
                        ->scalarNode('match')->end()
                        ->scalarNode('unmatch')->end()
                        ->scalarNode('match_mapping_type')->end()
                        ->scalarNode('path_match')->end()
                        ->scalarNode('path_unmatch')->end()
                        ->scalarNode('match_pattern')->end()
                        ->arrayNode('mapping')
                            ->prototype('variable')
                                ->treatNullLike(array())
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    /**
     * Returns the array node used for "_id".
     */
    protected function getIdNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('_id');

        $node
            ->children()
            ->scalarNode('path')->end()
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
                ->scalarNode('compress')->end()
                ->scalarNode('compress_threshold')->end()
                ->scalarNode('enabled')->defaultTrue()->end()
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

    /**
     * Returns the array node used for "_routing".
     */
    protected function getRoutingNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('_routing');

        $node
            ->children()
                ->scalarNode('required')->end()
                ->scalarNode('path')->end()
            ->end()
        ;

        return $node;
    }

    /**
     * Returns the array node used for "_parent".
     */
    protected function getParentNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('_parent');

        $node
            ->children()
                ->scalarNode('type')->end()
                ->scalarNode('property')->defaultValue(null)->end()
                ->scalarNode('identifier')->defaultValue('id')->end()
            ->end()
        ;

        return $node;
    }

    /**
     * Returns the array node used for "_all"
     */
    protected function getAllNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('_all');

        $node
            ->children()
            ->scalarNode('enabled')->defaultValue(true)->end()
            ->scalarNode('index_analyzer')->end()
            ->scalarNode('search_analyzer')->end()
            ->end()
        ;

        return $node;
    }

    /**
     * Returns the array node used for "_timestamp"
     */
    protected function getTimestampNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('_timestamp');

        $node
            ->children()
            ->scalarNode('enabled')->defaultValue(true)->end()
            ->scalarNode('path')->end()
            ->scalarNode('format')->end()
            ->scalarNode('store')->end()
            ->scalarNode('index')->end()
            ->end()
        ;

        return $node;
    }

    /**
     * Returns the array node used for "_ttl"
     */
    protected function getTtlNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('_ttl');

        $node
            ->children()
            ->scalarNode('enabled')->defaultValue(true)->end()
            ->scalarNode('default')->end()
            ->scalarNode('store')->end()
            ->scalarNode('index')->end()
            ->end()
        ;

        return $node;
    }

    /**
     * @return ArrayNodeDefinition|\Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    protected function getPersistenceNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('persistence');

        $node
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
                        ->scalarNode('flush')->defaultTrue()->end()
                        ->booleanNode('immediate')->defaultFalse()->end()
                        ->scalarNode('logger')
                            ->defaultFalse()
                            ->treatNullLike('fos_elastica.logger')
                            ->treatTrueLike('fos_elastica.logger')
                        ->end()
                        ->scalarNode('service')->end()
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
                        ->scalarNode('ignore_missing')->defaultFalse()->end()
                        ->scalarNode('query_builder_method')->defaultValue('createQueryBuilder')->end()
                        ->scalarNode('service')->end()
                    ->end()
                ->end()
                ->arrayNode('model_to_elastica_transformer')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('service')->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }

    /**
     * @return ArrayNodeDefinition|\Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    protected function getSerializerNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('serializer');

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('groups')
                    ->treatNullLike(array())
                    ->prototype('scalar')->end()
                ->end()
                ->scalarNode('version')->end()
            ->end();

        return $node;
    }
}
