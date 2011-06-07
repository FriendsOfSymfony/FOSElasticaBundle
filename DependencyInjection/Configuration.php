<?php

namespace FOQ\ElasticaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration
{
    /**
     * Generates the configuration tree.
     *
     * @return \Symfony\Component\DependencyInjection\Configuration\NodeInterface
     */
    public function getConfigTree()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('foq_elastica', 'array');

        $this->addClientsSection($rootNode);
        $this->addIndexesSection($rootNode);

        $rootNode
            ->children()
                ->scalarNode('default_client')->end()
                ->scalarNode('default_index')->end()
            ->end()
        ;

        return $treeBuilder->buildTree();
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
                        ->children()
                            ->scalarNode('host')->defaultValue('localhost')->end()
                            ->scalarNode('port')->defaultValue('9000')->end()
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
                            ->arrayNode('type_prototype')
                                ->children()
                                    ->arrayNode('doctrine')
                                        ->children()
                                            ->scalarNode('driver')->end()
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
                    ->arrayNode('doctrine')
                        ->children()
                            ->scalarNode('driver')->end()
                            ->scalarNode('model')->end()
                            ->scalarNode('identifier')->defaultValue('id')->end()
                            ->arrayNode('provider')
                                ->children()
                                    ->scalarNode('query_builder_method')->defaultValue('createQueryBuilder')->end()
                                    ->scalarNode('batch_size')->defaultValue(100)->end()
                                    ->scalarNode('clear_object_manager')->defaultTrue()->end()
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
                ->end()
            ->end()
        ;

        return $node;
    }
}
