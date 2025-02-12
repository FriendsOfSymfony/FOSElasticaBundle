<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Index;

use Elastic\Elasticsearch\Exception\ElasticsearchException;
use Elastica\Client;
use FOS\ElasticaBundle\Configuration\IndexConfig;
use FOS\ElasticaBundle\Elastica\Index;
use FOS\ElasticaBundle\Exception\AliasIsIndexException;

class AliasProcessor
{
    /**
     * Sets the randomised root name for an index.
     *
     * @return void
     */
    public function setRootName(IndexConfig $indexConfig, Index $index)
    {
        $index->overrideName(
            \sprintf(
                '%s_%s',
                $indexConfig->getElasticSearchName(),
                \date('Y-m-d-His')
            )
        );
    }

    /**
     * Switches an index to become the new target for an alias. Only applies for
     * indexes that are set to use aliases.
     *
     * $force will delete an index encountered where an alias is expected.
     *
     * @throws AliasIsIndexException
     *
     * @return void
     */
    public function switchIndexAlias(IndexConfig $indexConfig, Index $index, bool $force = false, bool $delete = true)
    {
        $client = $index->getClient();

        $aliasName = $indexConfig->getElasticSearchName();
        $oldIndexName = null;
        $newIndexName = $index->getName();

        try {
            $oldIndexName = $this->getAliasedIndex($client, $aliasName);
        } catch (AliasIsIndexException $e) {
            if (!$force) {
                throw $e;
            }

            if ($delete) {
                $this->deleteIndex($index, $aliasName);
            } else {
                $this->closeIndex($index, $aliasName);
            }
        }

        try {
            $aliasUpdateRequest = $this->buildAliasUpdateRequest($oldIndexName, $aliasName, $newIndexName);
            $client->indices()->updateAliases(['body' => $aliasUpdateRequest]);
        } catch (ElasticsearchException $e) {
            $this->cleanupRenameFailure($index, $newIndexName, $e);
        }

        // Delete the old index after the alias has been switched
        if (null !== $oldIndexName) {
            if ($delete) {
                $this->deleteIndex($index, $oldIndexName);
            } else {
                $this->closeIndex($index, $oldIndexName);
            }
        }
    }

    /**
     * Builds an ElasticSearch request to rename or create an alias.
     *
     * @return array{actions: list<mixed>}
     */
    private function buildAliasUpdateRequest(?string $aliasedIndex, string $aliasName, string $newIndexName): array
    {
        $aliasUpdateRequest = ['actions' => []];
        if (null !== $aliasedIndex) {
            // if the alias is set - add an action to remove it
            $aliasUpdateRequest['actions'][] = [
                'remove' => ['index' => $aliasedIndex, 'alias' => $aliasName],
            ];
        }

        // add an action to point the alias to the new index
        $aliasUpdateRequest['actions'][] = [
            'add' => ['index' => $newIndexName, 'alias' => $aliasName],
        ];

        return $aliasUpdateRequest;
    }

    /**
     * Cleans up an index when we encounter a failure to rename the alias.
     */
    private function cleanupRenameFailure(Index $index, string $indexName, \Throwable $renameAliasException): void
    {
        $additionalError = '';
        try {
            $this->deleteIndex($index, $indexName);
        } catch (\Throwable $deleteNewIndexException) {
            $additionalError = \sprintf(
                'Tried to delete newly built index %s, but also failed: %s',
                $indexName,
                $deleteNewIndexException->getMessage()
            );
        }

        throw new \RuntimeException(\sprintf('Failed to updated index alias: %s. %s', $renameAliasException->getMessage(), $additionalError ?: \sprintf('Newly built index %s was deleted', $indexName)), 0, $renameAliasException);
    }

    /**
     * Delete an index.
     */
    private function deleteIndex(Index $index, string $indexName): void
    {
        try {
            $index->getClient()->indices()->delete(['index' => $indexName]);
        } catch (ElasticsearchException $deleteOldIndexException) {
            throw new \RuntimeException(\sprintf('Failed to delete index "%s" with message: "%s"', $indexName, $deleteOldIndexException->getMessage()), 0, $deleteOldIndexException);
        }
    }

    /**
     * Close an index.
     */
    private function closeIndex(Index $index, string $indexName): void
    {
        try {
            $index->getClient()->indices()->close(['index' => $indexName]);
        } catch (ElasticsearchException $e) {
            throw new \RuntimeException(\sprintf('Failed to close index "%s" with message: "%s"', $indexName, $e->getMessage()), 0, $e);
        }
    }

    /**
     * Returns the name of a single index that an alias points to or throws
     * an exception if there is more than one.
     *
     * @throws AliasIsIndexException
     */
    private function getAliasedIndex(Client $client, string $aliasName): ?string
    {
        $response = $client->indices()->getAlias(['name' => '*']);
        $aliasesInfo = $response->asArray();
        $aliasedIndexes = [];

        foreach ($aliasesInfo as $indexName => $indexInfo) {
            if ($indexName === $aliasName) {
                throw new AliasIsIndexException($indexName);
            }
            if (!isset($indexInfo['aliases'])) {
                continue;
            }

            $aliases = \array_keys($indexInfo['aliases']);
            if (\in_array($aliasName, $aliases, true)) {
                $aliasedIndexes[] = $indexName;
            }
        }

        if (\count($aliasedIndexes) > 1) {
            throw new \RuntimeException(\sprintf('Alias "%s" is used for multiple indexes: ["%s"]. Make sure it\'s either not used or is assigned to one index only', $aliasName, \implode('", "', $aliasedIndexes)));
        }

        return \array_shift($aliasedIndexes);
    }
}
