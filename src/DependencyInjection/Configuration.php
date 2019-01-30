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

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    const SUPPORTED_DRIVERS = ['orm', 'mongodb', 'phpcr'];

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
        $treeBuilder = new TreeBuilder('fos_elastica');

        if (method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $rootNode = $treeBuilder->root('fos_elastica');
        }

        $this->addClientsSection($rootNode);
        $this->addIndexesSection($rootNode);
        $this->addIndexTemplatesSection($rootNode);

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
                    ->treatNullLike([])
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
     * Returns the array node used for "dynamic_templates".
     */
    private function getDynamicTemplateNode()
    {
        $node = $this->createTreeBuilderNode('dynamic_templates');

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
                                ->treatNullLike([])
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    /**
     * Returns the array node used for "types".
     */
    private function getTypesNode()
    {
        $node = $this->createTreeBuilderNode('types');

        $node
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->treatNullLike([])
                ->beforeNormalization()
                ->ifNull()
                    ->thenEmptyArray()
                ->end()
                // Support multiple dynamic_template formats to match the old bundle style
                // and the way ElasticSearch expects them
                ->beforeNormalization()
                ->ifTrue(function ($v) {
                    return isset($v['dynamic_templates']);
                })
                ->then(function ($v) {
                    $dt = [];
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
                    ->booleanNode('date_detection')->end()
                    ->arrayNode('dynamic_date_formats')->prototype('scalar')->end()->end()
                    ->scalarNode('analyzer')->end()
                    ->booleanNode('numeric_detection')->end()
                    ->scalarNode('dynamic')->end()
                    ->variableNode('indexable_callback')->end()
                    ->append($this->getPersistenceNode())
                    ->append($this->getSerializerNode())
                ->end()
                ->append($this->getIdNode())
                ->append($this->getPropertiesNode())
                ->append($this->getDynamicTemplateNode())
                ->append($this->getSourceNode())
                ->append($this->getRoutingNode())
                ->append($this->getParentNode())
                ->append($this->getAllNode())
            ->end()
        ;

        return $node;
    }

    /**
     * Returns the array node used for "properties".
     */
    private function getPropertiesNode()
    {
        $node = $this->createTreeBuilderNode('properties');

        $node
            ->useAttributeAsKey('name')
            ->prototype('variable')
                ->treatNullLike([]);

        return $node;
    }

    /**
     * Returns the array node used for "_id".
     */
    private function getIdNode()
    {
        $node = $this->createTreeBuilderNode('_id');

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
    private function getSourceNode()
    {
        $node = $this->createTreeBuilderNode('_source');

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
     * Returns the array node used for "_routing".
     */
    private function getRoutingNode()
    {
        $node = $this->createTreeBuilderNode('_routing');

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
    private function getParentNode()
    {
        $node = $this->createTreeBuilderNode('_parent');

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
     * Returns the array node used for "_all".
     */
    private function getAllNode()
    {
        $node = $this->createTreeBuilderNode('_all');

        $node
            ->children()
            ->scalarNode('enabled')->defaultValue(true)->end()
            ->scalarNode('analyzer')->end()
            ->end()
        ;

        return $node;
    }

    /**
     * @return ArrayNodeDefinition|\Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    private function getPersistenceNode()
    {
        $node = $this->createTreeBuilderNode('persistence');

        $node
            ->validate()
                ->ifTrue(function ($v) {
                    return isset($v['driver']) && 'orm' !== $v['driver'] && !empty($v['elastica_to_model_transformer']['hints']);
                })
                    ->thenInvalid('Hints are only supported by the "orm" driver')
            ->end()
            ->children()
                ->scalarNode('driver')
                    ->defaultValue('orm')
                    ->validate()
                    ->ifNotInArray(self::SUPPORTED_DRIVERS)
                        ->thenInvalid('The driver %s is not supported. Please choose one of '.json_encode(self::SUPPORTED_DRIVERS))
                    ->end()
                ->end()
                ->scalarNode('model')->defaultValue(null)->end()
                ->scalarNode('repository')->end()
                ->scalarNode('identifier')->defaultValue('id')->end()
                ->arrayNode('provider')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('batch_size')->defaultValue(100)->end()
                        ->scalarNode('clear_object_manager')->defaultTrue()->end()
                        ->scalarNode('debug_logging')
                            ->defaultValue($this->debug)
                            ->treatNullLike(true)
                        ->end()
                        ->scalarNode('query_builder_method')->defaultValue('createQueryBuilder')->end()
                        ->scalarNode('service')->end()
                    ->end()
                ->end()
                ->arrayNode('listener')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->end()
                        ->scalarNode('insert')->defaultTrue()->end()
                        ->scalarNode('update')->defaultTrue()->end()
                        ->scalarNode('delete')->defaultTrue()->end()
                        ->scalarNode('flush')->defaultTrue()->end()
                        ->booleanNode('defer')->defaultFalse()->end()
                        ->scalarNode('logger')
                            ->defaultFalse()
                            ->treatNullLike('fos_elastica.logger')
                            ->treatTrueLike('fos_elastica.logger')
                        ->end()
                        ->scalarNode('service')->end()
                    ->end()
                ->end()
                ->arrayNode('finder')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('service')->end()
                    ->end()
                ->end()
                ->arrayNode('elastica_to_model_transformer')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('hints')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('name')->end()
                                    ->scalarNode('value')->end()
                                ->end()
                            ->end()
                        ->end()
                        ->booleanNode('hydrate')->defaultTrue()->end()
                        ->booleanNode('ignore_missing')
                            ->defaultFalse()
                            ->info('Silently ignore results returned from Elasticsearch without corresponding persistent object.')
                        ->end()
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
                ->arrayNode('persister')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->enumNode('refresh')
                            ->treatTrueLike('true')
                            ->treatFalseLike('false')
                            ->values(['true', 'wait_for', 'false'])
                        ->end()
                        ->scalarNode('service')->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }

    /**
     * @return ArrayNodeDefinition|\Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    private function getSerializerNode()
    {
        $node = $this->createTreeBuilderNode('serializer');

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('groups')
                    ->treatNullLike([])
                    ->prototype('scalar')->end()
                ->end()
                ->scalarNode('version')->end()
                ->booleanNode('serialize_null')
                    ->defaultFalse()
                ->end()
            ->end();

        return $node;
    }

    /**
     * Adds the configuration for the "clients" key.
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
                        // Elastica names its properties with camel case, support both
                        ->beforeNormalization()
                        ->ifTrue(function ($v) {
                            return isset($v['connection_strategy']);
                        })
                        ->then(function ($v) {
                            $v['connectionStrategy'] = $v['connection_strategy'];
                            unset($v['connection_strategy']);

                            return $v;
                        })
                        ->end()
                        // If there is no connections array key defined, assume a single connection.
                        ->beforeNormalization()
                        ->ifTrue(function ($v) {
                            return is_array($v) && !array_key_exists('connections', $v);
                        })
                        ->then(function ($v) {
                            return [
                                'connections' => [$v],
                            ];
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
                                                ->ifTrue(function ($url) {
                                                    return $url && '/' !== substr($url, -1);
                                                })
                                                ->then(function ($url) {
                                                    return $url.'/';
                                                })
                                            ->end()
                                        ->end()
                                        ->scalarNode('username')->end()
                                        ->scalarNode('password')->end()
                                        ->scalarNode('host')->end()
                                        ->scalarNode('port')->end()
                                        ->scalarNode('proxy')->end()
                                        ->arrayNode('http_error_codes')
                                            ->beforeNormalization()
                                                ->ifTrue(function ($v) { return !is_array($v); })
                                                ->then(function ($v) { return array($v); })
                                            ->end()
                                            ->cannotBeEmpty()
                                            ->defaultValue([400, 403, 404])
                                            ->prototype('scalar')->end()
                                        ->end()
                                        ->scalarNode('aws_access_key_id')->end()
                                        ->scalarNode('aws_secret_access_key')->end()
                                        ->scalarNode('aws_region')->end()
                                        ->scalarNode('aws_session_token')->end()
                                        ->booleanNode('ssl')->defaultValue(false)->end()
                                        ->scalarNode('logger')
                                            ->defaultValue($this->debug ? 'fos_elastica.logger' : false)
                                            ->treatNullLike('fos_elastica.logger')
                                            ->treatTrueLike('fos_elastica.logger')
                                        ->end()
                                        ->booleanNode('compression')->defaultValue(false)->end()
                                        ->arrayNode('headers')
                                            ->normalizeKeys(false)
                                            ->useAttributeAsKey('name')
                                            ->prototype('scalar')->end()
                                        ->end()
                                        ->arrayNode('curl')
                                            ->useAttributeAsKey(CURLOPT_SSL_VERIFYPEER)
                                            ->prototype('boolean')->end()
                                        ->end()
                                        ->scalarNode('transport')->end()
                                        ->scalarNode('timeout')->end()
                                        ->scalarNode('connectTimeout')->end()
                                        ->scalarNode('retryOnConflict')
                                            ->defaultValue(0)
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->scalarNode('timeout')->end()
                            ->scalarNode('connectTimeout')->end()
                            ->scalarNode('headers')->end()
                            ->scalarNode('connectionStrategy')->defaultValue('Simple')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * Adds the configuration for the "indexes" key.
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
                                    ->scalarNode('analyzer')->end()
                                    ->append($this->getPersistenceNode())
                                    ->append($this->getSerializerNode())
                                ->end()
                            ->end()
                            ->variableNode('settings')->defaultValue([])->end()
                        ->end()
                        ->append($this->getTypesNode())
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * @return ArrayNodeDefinition|\Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    private function createTreeBuilderNode($name)
    {
        $builder = new TreeBuilder($name);

        if (method_exists($builder, 'getRootNode')) {
            $node = $builder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $node = $builder->root($name);
        }

        return $node;
    }

    /**
     * Adds the configuration for the "index_templates" key.
     *
     * @param ArrayNodeDefinition $rootNode
     *
     * @return void
     */
    private function addIndexTemplatesSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->fixXmlConfig('index_template')
            ->children()
                ->arrayNode('index_templates')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('template_name')
                                ->info('Defaults to the name of the index template, but can be modified if the index name is different in ElasticSearch')
                            ->end()
                            ->scalarNode('template')->isRequired()->end()
                            ->scalarNode('client')->end()
                            ->variableNode('settings')->defaultValue([])->end()
                        ->end()
                        ->append($this->getTypesNode())
                    ->end()
                ->end()
            ->end()
        ;
    }
}
