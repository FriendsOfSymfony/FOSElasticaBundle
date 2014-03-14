<?php

namespace FOS\ElasticaBundle;

use Elastica\Exception\ResponseException;
use Elastica\Type\Mapping;

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
     * @throws ResponseException
     */
    public function resetIndexType($indexName, $typeName)
    {
        $indexConfig = $this->getIndexConfig($indexName);

        if (!isset($indexConfig['config']['mappings'][$typeName]['properties'])) {
            throw new \InvalidArgumentException(sprintf('The mapping for index "%s" and type "%s" does not exist.', $indexName, $typeName));
        }

        $type = $indexConfig['index']->getType($typeName);
        try {
            $type->delete();
        } catch (ResponseException $e) {
            if (strpos($e->getMessage(), 'TypeMissingException') === false) {
                throw $e;
            }
        }
        $mapping = $this->createMapping($indexConfig['config']['mappings'][$typeName]);
        $type->setMapping($mapping);
    }

    /**
     * create type mapping object
     *
     * @param array $indexConfig
     * @return Mapping
     */
    protected function createMapping($indexConfig)
    {
        $mapping = Mapping::create($indexConfig['properties']);

        $mappingSpecialFields = array('_uid', '_id', '_source', '_all', '_analyzer', '_boost', '_routing', '_index', '_size', '_timestamp', '_ttl', 'dynamic_templates');
        foreach ($mappingSpecialFields as $specialField) {
            if (isset($indexConfig[$specialField])) {
                $mapping->setParam($specialField, $indexConfig[$specialField]);
            }
        }

        if (isset($indexConfig['_parent'])) {
            $mapping->setParam('_parent', array('type' => $indexConfig['_parent']['type']));
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
