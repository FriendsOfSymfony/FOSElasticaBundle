<?php

namespace FOS\ElasticaBundle;

/**
 * Deletes and recreates indexes
 */
class Resetter
{
    protected $indexConfigsByName;

    /**
     * Constructor.
     *
     * @param array $indexConfigsByName
     */
    public function __construct(array $indexConfigsByName)
    {
        $this->indexConfigsByName = $indexConfigsByName;
    }

    /**
     * Deletes and recreates all indexes
     */
    public function resetAllIndexes()
    {
        foreach ($this->indexConfigsByName as $indexConfig) {
            $indexConfig['index']->create($indexConfig['config'], true);
        }
    }

    /**
     * Deletes and recreates the named index
     *
     * @param string $indexName
     * @throws \InvalidArgumentException if no index exists for the given name
     */
    public function resetIndex($indexName)
    {
        $indexConfig = $this->getIndexConfig($indexName);
        $indexConfig['index']->create($indexConfig['config'], true);
    }

    /**
     * Deletes and recreates a mapping type for the named index
     *
     * @param string $indexName
     * @param string $typeName
     * @throws \InvalidArgumentException if no index or type mapping exists for the given names
     */
    public function resetIndexType($indexName, $typeName)
    {
        $indexConfig = $this->getIndexConfig($indexName);

        if (!isset($indexConfig['config']['mappings'][$typeName]['properties'])) {
            throw new \InvalidArgumentException(sprintf('The mapping for index "%s" and type "%s" does not exist.', $indexName, $typeName));
        }

        $type = $indexConfig['index']->getType($typeName);
        $type->delete();
        $mapping = $this->createMapping($indexConfig['config']['mappings'][$typeName]);
        $type->setMapping($mapping);
    }

    /**
     * create type mapping object
     *
     * @param array $indexConfig
     * @return \Elastica_Type_Mapping
     */
    protected function createMapping($indexConfig)
    {
        $mapping = \Elastica_Type_Mapping::create($indexConfig['properties']);

        foreach($indexConfig['properties'] as $type) {
            if (!empty($type['_parent']) && $type['_parent'] !== '~') {
                $mapping->setParam('_parent', array('type' => $type['_parent']['type']));
            }
        }

        if (isset($indexConfig['dynamic_templates'])) {
            $mapping->setParam('dynamic_templates', $indexConfig['dynamic_templates']);
        }

        return $mapping;
    }

    /**
     * Gets an index config by its name
     *
     * @param string $indexName Index name
     *
     * @param $indexName
     * @return array
     * @throws \InvalidArgumentException if no index config exists for the given name
     */
    protected function getIndexConfig($indexName)
    {
        if (!isset($this->indexConfigsByName[$indexName])) {
            throw new \InvalidArgumentException(sprintf('The configuration for index "%s" does not exist.', $indexName));
        }

        return $this->indexConfigsByName[$indexName];
    }
}
