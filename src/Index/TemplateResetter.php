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

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastica\Client;
use Elastica\IndexTemplate;
use FOS\ElasticaBundle\Configuration\IndexTemplateConfig;
use FOS\ElasticaBundle\Configuration\ManagerInterface;

/**
 * Class Template resetter.
 *
 * @author Dmitry Balabka <dmitry.balabka@intexsys.lv>
 */
class TemplateResetter implements ResetterInterface
{
    private ManagerInterface $configManager;
    private MappingBuilder $mappingBuilder;
    private IndexTemplateManager $indexTemplateManager;

    public function __construct(
        ManagerInterface $configManager,
        MappingBuilder $mappingBuilder,
        IndexTemplateManager $indexTemplateManager
    ) {
        $this->configManager = $configManager;
        $this->mappingBuilder = $mappingBuilder;
        $this->indexTemplateManager = $indexTemplateManager;
    }

    public function resetAllIndexes(bool $deleteIndexes = false): void
    {
        foreach ($this->configManager->getIndexNames() as $name) {
            $this->resetIndex($name, $deleteIndexes);
        }
    }

    public function resetIndex(string $indexName, bool $deleteIndexes = false): void
    {
        $indexTemplateConfig = $this->configManager->getIndexConfiguration($indexName);
        if (!$indexTemplateConfig instanceof IndexTemplateConfig) {
            throw new \RuntimeException(\sprintf('Incorrect index configuration object. Expecting IndexTemplateConfig, but got: %s ', \get_class($indexTemplateConfig)));
        }
        $indexTemplate = $this->indexTemplateManager->getIndexTemplate($indexName);

        $mapping = $this->mappingBuilder->buildIndexTemplateMapping($indexTemplateConfig);
        if ($deleteIndexes) {
            $this->deleteTemplateIndexes($indexTemplateConfig, $indexTemplate->getClient());

            try {
                $indexTemplate->delete();
            } catch (ClientResponseException $e) {
                if ($e->getResponse()->getStatusCode() === 404) {
                    // Template does not exist, so can not be removed.
                } else {
                    throw $e;
                }
            }
        }
        $indexTemplate->create($mapping);
    }

    /**
     * Delete all template indexes.
     */
    public function deleteTemplateIndexes(IndexTemplateConfig $template, Client $client): void
    {
        foreach ($template->getIndexPatterns() as $pattern) {
            try {
                $client->indices()->delete(['index' => $pattern . '/']);
            } catch (ClientResponseException $e) {
                if ($e->getResponse()->getStatusCode() === 404) {
                    // Index does not exist, so can not be removed.
                } else {
                    throw $e;
                }
            }
        }
    }
}
