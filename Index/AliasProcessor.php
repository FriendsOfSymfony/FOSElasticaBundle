<?php

/**
 * This file is part of the FOSElasticaBundle project.
 *
 * (c) Infinite Networks Pty Ltd <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Index;

use Elastica\Client;
use Elastica\Exception\ExceptionInterface;
use Elastica\Request;
use FOS\ElasticaBundle\Configuration\IndexConfig;
use FOS\ElasticaBundle\Elastica\Index;
use FOS\ElasticaBundle\Exception\AliasIsIndexException;

class AliasProcessor
{
    /**
     * Sets the randomised root name for an index.
     *
     * @param IndexConfig $indexConfig
     * @param Index $index
     */
    public function setRootName(IndexConfig $indexConfig, Index $index)
    {
        $index->overrideName(
            sprintf('%s_%s',
                $indexConfig->getElasticSearchName(),
                date('Y-m-d-His')
            )
        );
    }

    /**
     * Switches an index to become the new target for an alias. Only applies for
     * indexes that are set to use aliases.
     *
     * $force will delete an index encountered where an alias is expected.
     *
     * @param IndexConfig $indexConfig
     * @param Index $index
     * @param bool $force
     * @throws AliasIsIndexException
     * @throws \RuntimeException
     */
    public function switchIndexAlias(IndexConfig $indexConfig, Index $index, $force = false, $deleteOldIndex = true)
    {
        $client = $index->getClient();

        $aliasName = $indexConfig->getElasticSearchName();
        $oldIndexName = false;
        $newIndexName = $index->getName();

        try {
            $aliasedIndexes = $this->getAliasedIndexes($client, $aliasName);
        } catch(AliasIsIndexException $e) {
            if (!$force) {
                throw $e;
            }

            $this->deleteIndex($client, $aliasName);
            $aliasedIndexes = array();
        }

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

        $aliasUpdateRequest = array('actions' => array());
        if (count($aliasedIndexes) === 1) {
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
            $client->request('_aliases', 'POST', $aliasUpdateRequest);
        } catch (ExceptionInterface $renameAliasException) {
            $additionalError = '';
            // if we failed to move the alias, delete the newly built index
            try {
                $index->delete();
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
                ), 0, $renameAliasException
            );
        }

        // Delete the old index after the alias has been switched
        if ($oldIndexName && $deleteOldIndex) {
            $oldIndex = new Index($client, $oldIndexName);
            try {
                $oldIndex->delete();
            } catch (ExceptionInterface $deleteOldIndexException) {
                throw new \RuntimeException(
                    sprintf(
                        'Failed to delete old index %s with message: %s',
                        $oldIndexName,
                        $deleteOldIndexException->getMessage()
                    ), 0, $deleteOldIndexException
                );
            }
        }
    }

    /**
     * Returns array of indexes which are mapped to given alias
     *
     * @param Client $client
     * @param string $aliasName Alias name
     *
     * @return array
     * @throws AliasIsIndexException
     */
    public function getAliasedIndexes(Client $client, $aliasName)
    {
        $aliasesInfo = $client->request('_aliases', 'GET')->getData();
        $aliasedIndexes = array();

        foreach ($aliasesInfo as $indexName => $indexInfo) {
            if ($indexName === $aliasName) {
                throw new AliasIsIndexException($indexName);
            }
            if (!isset($indexInfo['aliases'])) {
                continue;
            }

            $aliases = array_keys($indexInfo['aliases']);
            if (in_array($aliasName, $aliases)) {
                $aliasedIndexes[] = $indexName;
            }
        }

        return $aliasedIndexes;
    }

    /**
     * Delete an index
     *
     * @param Client $client
     * @param string $indexName Index name to delete
     */
    private function deleteIndex(Client $client, $indexName)
    {
        $path = sprintf("%s", $indexName);
        $client->request($path, Request::DELETE);
    }
}
