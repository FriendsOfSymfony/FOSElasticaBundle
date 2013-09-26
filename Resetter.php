<?php

namespace FOS\ElasticaBundle;

use Elastica\Exception\ExceptionInterface;
use Elastica\Index;
use Elastica\Type\Mapping;

/**
 * Deletes and recreates indexes
 */
class Resetter
{
    /**
     * Index Manager
     *
     * @var \FOQ\ElasticaBundle\IndexManager
     */
    protected $indexManager;

    /**
     * Constructor.
     *
     * @param IndexManager $indexManager
     */
    public function __construct(IndexManager $indexManager)
    {
        $this->indexManager = $indexManager;
    }

    /**
     * Deletes and recreates all indexes
     */
    public function resetAllIndexes()
    {
        foreach ($this->indexManager->getAllIndexes() as $name) {
            $this->resetIndex($name);
        }
    }

    /**
     * Deletes and recreates the named index
     *
     * @param string $indexKey
     *
     * @throws \InvalidArgumentException if no index exists for the given name
     */
    public function resetIndex($indexKey)
    {
        $indexConfig = $this->indexManager->getIndexConfig($indexKey);
        $esIndex = $this->indexManager->getIndex($indexKey);

        if (isset($indexConfig['use_alias']) && $indexConfig['use_alias']) {
            $name = $indexConfig['name_or_alias'];
            $name .= date('-Y-m-d-Gis');
            $esIndex->overrideName($name);
            $esIndex->create($indexConfig['config']);
        } else {
            $esIndex->create($indexConfig['config'], true);
        }
    }

    public function postPopulate($indexKey)
    {
        $indexConfig = $this->indexManager->getIndexConfig($indexKey);
        if (isset($indexConfig['use_alias']) && $indexConfig['use_alias']) {
            $this->switchIndexAlias($indexKey);
        }
    }

    /**
     * Switches the alias for given index (by key) to the newly populated index
     * and deletes the old index
     *
     * @param string $indexKey Index key
     *
     * @throws \RuntimeException
     */
    private function switchIndexAlias($indexKey)
    {
        $indexConfig = $this->indexManager->getIndexConfig($indexKey);
        $esIndex = $this->indexManager->getIndex($indexKey);
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
                    $deleteNewIndexException->getError()
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

    /**
     * Deletes and recreates a mapping type for the named index
     *
     * @param string $indexKey
     * @param string $typeName
     * @throws \InvalidArgumentException if no index or type mapping exists for the given names
     */
    public function resetIndexType($indexKey, $typeName)
    {
        $indexConfig = $this->indexManager->getIndexConfig($indexKey);
        $esIndex = $this->indexManager->getIndex($indexKey);

        if (!isset($indexConfig['config']['mappings'][$typeName]['properties'])) {
            throw new \InvalidArgumentException(sprintf('The mapping for index "%s" and type "%s" does not exist.', $indexKey, $typeName));
        }

        $type = $esIndex->getType($typeName);

        // @TODO add type exists check, otherwise it fails when creating new types in existing index
        $type->delete();
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

        if (isset($indexConfig['_parent'])) {
            $mapping->setParam('_parent', array('type' => $indexConfig['_parent']['type']));
        }

        return $mapping;
    }
}
