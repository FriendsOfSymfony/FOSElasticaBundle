<?php

namespace FOS\ElasticaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    private $supportedDrivers = array('orm', 'mongodb', 'propel');

    private $configArray = array();

    public function __construct($configArray)
    {
        $this->configArray = $configArray;
    }

    /**
     * Generates the configuration tree.
     *
     * @return \Symfony\Component\Config\Definition\NodeInterface
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('fos_elastica', 'array');

        $this->addClientsSection($rootNode);
        $this->addIndexesSection($rootNode);

        $rootNode
            ->children()
                ->scalarNode('default_client')->end()
                ->scalarNode('default_index')->end()
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
     * Generates the configuration tree.
     *
     * @return \Symfony\Component\DependencyInjection\Configuration\NodeInterface
     */
    public function getConfigTree()
    {
        return $this->getConfigTreeBuilder()->buildTree();
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
                                            'host'   => $v['host'],
                                            'port'   => $v['port'],
                                            'logger' => isset($v['logger']) ? $v['logger'] : null,
                                            'headers' => isset($v['headers']) ? $v['headers'] : null,
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
                                            'url'    => $v['url'],
                                            'logger' => isset($v['logger']) ? $v['logger'] : null
                                        )
                                    )
                                );
                            })
                        ->end()
                        ->children()
                            ->arrayNode('servers')
                                ->prototype('array')
                                    ->fixXmlConfig('header')
                                    ->children()
                                        ->scalarNode('url')
                                            ->validate()
                                                ->ifTrue(function($url) { return substr($url, -1) !== '/'; })
                                                ->then(function($url) { return $url.'/'; })
                                            ->end()
                                        ->end()
                                        ->scalarNode('host')->end()
                                        ->scalarNode('port')->end()
                                        ->scalarNode('logger')
                                            ->defaultValue('fos_elastica.logger')
                                            ->treatNullLike('fos_elastica.logger')
                                            ->treatTrueLike('fos_elastica.logger')
                                        ->end()
                                        ->arrayNode('headers')
                                            ->useAttributeAsKey('name')
                                            ->prototype('scalar')->end()
                                        ->end()
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
                            ->scalarNode('index_name')->end()
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
                                        ->end()
                                    ->end()
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
                ->children()
                    ->arrayNode('serializer')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->arrayNode('groups')
                                ->treatNullLike(array())
                                ->prototype('scalar')->end()
                            ->end()
                            ->scalarNode('version')->end()
                        ->end()
                    ->end()
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
                        ->end()
                    ->end()
                ->end()
                ->append($this->getIdNode())
                ->append($this->getMappingsNode())
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
     * Returns the array node used for "mappings".
     */
    protected function getMappingsNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('mappings');

        $nestings = $this->getNestings();

        $childrenNode = $node
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->treatNullLike(array())
                ->addDefaultsIfNotSet()
                ->children();

        $this->addFieldConfig($childrenNode, $nestings);

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
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->children()
                    ->scalarNode('match')->end()
                    ->scalarNode('unmatch')->end()
                    ->scalarNode('match_mapping_type')->end()
                    ->scalarNode('path_match')->end()
                    ->scalarNode('path_unmatch')->end()
                    ->scalarNode('match_pattern')->end()
                    ->append($this->getDynamicTemplateMapping())
                ->end()
            ->end()
        ;

        return $node;
    }

    /**
     * @return the array node used for mapping in dynamic templates
     */
    protected function getDynamicTemplateMapping()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('mapping');

        $nestings = $this->getNestingsForDynamicTemplates();

        $this->addFieldConfig($node->children(), $nestings);

        return $node;
    }

    /**
     * @param \Symfony\Component\Config\Definition\Builder\NodeBuilder $node The node to which to attach the field config to
     * @param array $nestings the nested mappings for the current field level
     */
    protected function addFieldConfig($node, $nestings)
    {
        $node
            ->scalarNode('type')->defaultValue('string')->end()
            ->scalarNode('boost')->end()
            ->scalarNode('store')->end()
            ->scalarNode('index')->end()
            ->scalarNode('index_analyzer')->end()
            ->scalarNode('search_analyzer')->end()
            ->scalarNode('analyzer')->end()
            ->scalarNode('term_vector')->end()
            ->scalarNode('null_value')->end()
            ->booleanNode('include_in_all')->defaultValue(true)->end()
            ->booleanNode('enabled')->defaultValue(true)->end()
            ->scalarNode('lat_lon')->end()
            ->scalarNode('index_name')->end()
            ->booleanNode('omit_norms')->end()
            ->scalarNode('index_options')->end()
            ->scalarNode('ignore_above')->end()
            ->scalarNode('position_offset_gap')->end()
            ->arrayNode('_parent')
                ->treatNullLike(array())
                ->children()
                    ->scalarNode('type')->end()
                    ->scalarNode('identifier')->defaultValue('id')->end()
                ->end()
            ->end()
            ->scalarNode('format')->end()
            ->scalarNode('similarity')->end();
        ;

        if (isset($nestings['fields'])) {
            $this->addNestedFieldConfig($node, $nestings, 'fields');
        }

        if (isset($nestings['properties'])) {
            $this->addNestedFieldConfig($node, $nestings, 'properties');
        }
    }

    /**
     * @param \Symfony\Component\Config\Definition\Builder\NodeBuilder $node The node to which to attach the nested config to
     * @param array $nestings The nestings for the current field level
     * @param string $property the name of the nested property ('fields' or 'properties')
     */
    protected function addNestedFieldConfig($node, $nestings, $property)
    {
        $childrenNode = $node
            ->arrayNode($property)
                ->useAttributeAsKey('name')
                ->prototype('array')
                    ->treatNullLike(array())
                    ->addDefaultsIfNotSet()
                    ->children();

        $this->addFieldConfig($childrenNode, $nestings[$property]);

        $childrenNode
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * @return array The unique nested mappings for all types
     */
    protected function getNestings()
    {
        if (!isset($this->configArray[0]['indexes'])) {
            return array();
        }

        $nestings = array();
        foreach ($this->configArray[0]['indexes'] as $index) {
            if (empty($index['types'])) {
                continue;
            }

            foreach ($index['types'] as $type) {
                if (empty($type['mappings'])) {
                    continue;
                }

                $nestings = array_merge_recursive($nestings, $this->getNestingsForType($type['mappings'], $nestings));
            }
        }
        return $nestings;
    }

    /**
     * @return array The unique nested mappings for all dynamic templates
     */
    protected function getNestingsForDynamicTemplates()
    {
        if (!isset($this->configArray[0]['indexes'])) {
            return array();
        }

        $nestings = array();
        foreach ($this->configArray[0]['indexes'] as $index) {
            if (empty($index['types'])) {
                continue;
            }

            foreach ($index['types'] as $type) {
                if (empty($type['dynamic_templates'])) {
                    continue;
                }

                foreach ($type['dynamic_templates'] as $definition) {
                    $field = $definition['mapping'];

                    if (isset($field['fields'])) {
                        $this->addPropertyNesting($field, $nestings, 'fields');
                    } else if (isset($field['properties'])) {
                        $this->addPropertyNesting($field, $nestings, 'properties');
                    }
                }

            }
        }
        return $nestings;
    }

    /**
     * @param array $mappings The mappings for the current type
     * @return array The nested mappings defined for this type
     */
    protected function getNestingsForType(array $mappings = null)
    {
        if ($mappings === null) {
            return array();
        }

        $nestings = array();

        foreach ($mappings as $field) {
            if (isset($field['fields'])) {
                $this->addPropertyNesting($field, $nestings, 'fields');
            } else if (isset($field['properties'])) {
                $this->addPropertyNesting($field, $nestings, 'properties');
            }
        }

        return $nestings;
    }

    /**
     * @param array $field      The field mapping definition
     * @param array $nestings   The nestings array
     * @param string $property  The nested property name ('fields' or 'properties')
     */
    protected function addPropertyNesting($field, &$nestings, $property)
    {
        if (!isset($nestings[$property])) {
            $nestings[$property] = array();
        }
        $nestings[$property] = array_merge_recursive($nestings[$property], $this->getNestingsForType($field[$property]));
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
                ->scalarNode('enabled')->end()
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
}
