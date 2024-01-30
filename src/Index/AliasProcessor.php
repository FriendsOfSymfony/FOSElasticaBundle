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
     * @return void
     *
     * @throws AliasIsIndexException
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
                $this->deleteIndex($client, $aliasName);
            } else {
                $this->closeIndex($client, $aliasName);
            }
        }

        try {
            $aliasUpdateRequest = $this->buildAliasUpdateRequest($oldIndexName, $aliasName, $newIndexName);
            $client->request('_aliases', 'POST', $aliasUpdateRequest);
        } catch (ExceptionInterface $e) {
            \assert($e instanceof \Throwable); // https://github.com/ruflin/Elastica/pull/2083
            $this->cleanupRenameFailure($client, $newIndexName, $e);
        }

        // Delete the old index after the alias has been switched
        if (null !== $oldIndexName) {
            if ($delete) {
                $this->deleteIndex($client, $oldIndexName);
            } else {
                $this->closeIndex($client, $oldIndexName);
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
    private function cleanupRenameFailure(Client $client, string $indexName, \Throwable $renameAliasException): void
    {
        $additionalError = '';
        try {
            $this->deleteIndex($client, $indexName);
        } catch (ExceptionInterface $deleteNewIndexException) {
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
    private function deleteIndex(Client $client, string $indexName): void
    {
        try {
            $path = $indexName;
            $client->request($path, Request::DELETE);
        } catch (ExceptionInterface $deleteOldIndexException) {
            throw new \RuntimeException(\sprintf('Failed to delete index "%s" with message: "%s"', $indexName, $deleteOldIndexException->getMessage()), 0, $deleteOldIndexException);
        }
    }

    /**
     * Close an index.
     */
    private function closeIndex(Client $client, string $indexName): void
    {
        try {
            $path = $indexName.'/_close';
            $client->request($path, Request::POST);
        } catch (ExceptionInterface $e) {
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
        $aliasesInfo = $client->request('_aliases', 'GET')->getData();
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
