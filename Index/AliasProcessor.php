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

use Elastica\Exception\ExceptionInterface;
use FOS\ElasticaBundle\Configuration\IndexConfig;
use FOS\ElasticaBundle\Elastica\Client;
use FOS\ElasticaBundle\Elastica\Index;

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
        $index->overrideName(sprintf('%s_%s', $indexConfig->getElasticSearchName(), uniqid()));
    }

    /**
     * Switches an index to become the new target for an alias. Only applies for
     * indexes that are set to use aliases.
     *
     * @param IndexConfig $indexConfig
     * @param Index $index
     * @throws \RuntimeException
     */
    public function switchIndexAlias(IndexConfig $indexConfig, Index $index)
    {
        $client = $index->getClient();

        $aliasName = $indexConfig->getElasticSearchName();
        $oldIndexName = false;
        $newIndexName = $index->getName();

        $aliasedIndexes = $this->getAliasedIndexes($client, $aliasName);

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
        if ($oldIndexName) {
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
     * @param string $aliasName Alias name
     * @return array
     */
    private function getAliasedIndexes(Client $client, $aliasName)
    {
        $aliasesInfo = $client->request('_aliases', 'GET')->getData();
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
