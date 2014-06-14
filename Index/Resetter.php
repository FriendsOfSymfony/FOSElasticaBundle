<?php

namespace FOS\ElasticaBundle\Index;

use Elastica\Exception\ExceptionInterface;
use Elastica\Index;
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
        foreach (array_keys($this->indexConfigsByName) as $name) {
            $this->resetIndex($name);
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
        $esIndex = $indexConfig['index'];
        if (isset($indexConfig['use_alias']) && $indexConfig['use_alias']) {
            $name = $indexConfig['name_or_alias'];
            $name .= uniqid();
            $esIndex->overrideName($name);
            $esIndex->create($indexConfig['config']);

            return;
        }

        $esIndex->create($indexConfig['config'], true);
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

        if (!isset($indexConfig['config']['properties'][$typeName]['properties'])) {
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
        $mapping = $this->createMapping($indexConfig['config']['properties'][$typeName]);
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

    public function postPopulate($indexName)
    {
        $indexConfig = $this->getIndexConfig($indexName);
        if (isset($indexConfig['use_alias']) && $indexConfig['use_alias']) {
            $this->switchIndexAlias($indexName);
        }
    }

    /**
     * Switches the alias for given index (by key) to the newly populated index
     * and deletes the old index
     *
     * @param string $indexName Index name
     *
     * @throws \RuntimeException
     */
    private function switchIndexAlias($indexName)
    {
        $indexConfig = $this->getIndexConfig($indexName);
        $esIndex = $indexConfig['index'];
        $aliasName = $indexConfig['name_or_alias'];
        $oldIndexName = false;
        $newIndexName = $esIndex->getName();

        $aliasedIndexes = $this->getAliasedIndexes($esIndex, $aliasName);

        if (count($aliasedIndexes) > 1) {
            throw new \RuntimeException(
                sprintf(
                    'Alias %s is used for multiple indexes: [%s].
                    Make sure it\'s either not used or is assigned to one index only',
                    $aliasName,
                    join(', ', $aliasedIndexes)
                )
            );
        }

        // Change the alias to point to the new index
        // Elastica's addAlias can't be used directly, because in current (0.19.x) version it's not atomic
        // In 0.20.x it's atomic, but it doesn't return the old index name
        $aliasUpdateRequest = array('actions' => array());
        if (count($aliasedIndexes) == 1) {
            // if the alias is set - add an action to remove it
            $oldIndexName = $aliasedIndexes[0];
            $aliasUpdateRequest['actions'][] = array(
                'remove' => array('index' => $oldIndexName, 'alias' => $aliasName)
            );
        }

        // add an action to point the alias to the new index
        $aliasUpdateRequest['actions'][] = array(
            'add' => array('index' => $newIndexName, 'alias' => $aliasName)
        );

        try {
            $esIndex->getClient()->request('_aliases', 'POST', $aliasUpdateRequest);
        } catch (ExceptionInterface $renameAliasException) {
            $additionalError   = '';
            // if we failed to move the alias, delete the newly built index
            try {
                $esIndex->delete();
            } catch (ExceptionInterface $deleteNewIndexException) {
                $additionalError = sprintf(
                    'Tried to delete newly built index %s, but also failed: %s',
                    $newIndexName,
                    $deleteNewIndexException->getMessage()
                );
            }

            throw new \RuntimeException(
                sprintf(
                    'Failed to updated index alias: %s. %s',
                    $renameAliasException->getMessage(),
                    $additionalError ?: sprintf('Newly built index %s was deleted', $newIndexName)
                )
            );
        }

        // Delete the old index after the alias has been switched
        if ($oldIndexName) {
            $oldIndex = new Index($esIndex->getClient(), $oldIndexName);
            try {
                $oldIndex->delete();
            } catch (ExceptionInterface $deleteOldIndexException) {
                throw new \RuntimeException(
                    sprintf(
                        'Failed to delete old index %s with message: %s',
                        $oldIndexName,
                        $deleteOldIndexException->getMessage()
                    )
                );
            }
        }
    }

    /**
     * Returns array of indexes which are mapped to given alias
     *
     * @param Index  $esIndex   ES Index
     * @param string $aliasName Alias name
     *
     * @return array
     */
    private function getAliasedIndexes(Index $esIndex, $aliasName)
    {
        $aliasesInfo = $esIndex->getClient()->request('_aliases', 'GET')->getData();
        $aliasedIndexes = array();

        foreach ($aliasesInfo as $indexName => $indexInfo) {
            $aliases = array_keys($indexInfo['aliases']);
            if (in_array($aliasName, $aliases)) {
                $aliasedIndexes[] = $indexName;
            }
        }

        return $aliasedIndexes;
    }
}
