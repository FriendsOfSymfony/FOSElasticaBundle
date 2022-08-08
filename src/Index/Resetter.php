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

use FOS\ElasticaBundle\Configuration\IndexConfig;
use FOS\ElasticaBundle\Configuration\ManagerInterface;
use FOS\ElasticaBundle\Event\PostIndexResetEvent;
use FOS\ElasticaBundle\Event\PreIndexResetEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Deletes and recreates indexes.
 */
class Resetter implements ResetterInterface
{
    private AliasProcessor $aliasProcessor;
    private ManagerInterface $configManager;
    private EventDispatcherInterface $dispatcher;
    private IndexManager $indexManager;
    private MappingBuilder $mappingBuilder;

    public function __construct(
        ManagerInterface $configManager,
        IndexManager $indexManager,
        AliasProcessor $aliasProcessor,
        MappingBuilder $mappingBuilder,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->aliasProcessor = $aliasProcessor;
        $this->configManager = $configManager;
        $this->dispatcher = $eventDispatcher;
        $this->indexManager = $indexManager;
        $this->mappingBuilder = $mappingBuilder;
    }

    /**
     * Deletes and recreates all indexes.
     */
    public function resetAllIndexes(bool $populating = false, bool $force = false): void
    {
        foreach ($this->configManager->getIndexNames() as $name) {
            $this->resetIndex($name, $populating, $force);
        }
    }

    /**
     * Deletes and recreates the named index. If populating, creates a new index
     * with a randomised name for an alias to be set after population.
     *
     * @throws \InvalidArgumentException if no index exists for the given name
     */
    public function resetIndex(string $indexName, bool $populating = false, bool $force = false): void
    {
        $indexConfig = $this->configManager->getIndexConfiguration($indexName);
        if (!$indexConfig instanceof IndexConfig) {
            throw new \RuntimeException(\sprintf('Incorrect index configuration object. Expecting IndexConfig, but got: %s ', \get_class($indexConfig)));
        }
        $index = $this->indexManager->getIndex($indexName);

        if ($indexConfig->isUseAlias()) {
            $this->aliasProcessor->setRootName($indexConfig, $index);
        }

        $this->dispatcher->dispatch($event = new PreIndexResetEvent($indexName, $populating, $force));

        $mapping = $this->mappingBuilder->buildIndexMapping($indexConfig);
        $index->create($mapping, ['recreate' => true]);

        if (!$populating && $indexConfig->isUseAlias()) {
            $this->aliasProcessor->switchIndexAlias($indexConfig, $index, $force);
        }

        $this->dispatcher->dispatch(new PostIndexResetEvent($indexName, $populating, $force));
    }

    /**
     * Switch index alias.
     *
     * @throws \FOS\ElasticaBundle\Exception\AliasIsIndexException
     */
    public function switchIndexAlias(string $indexName, bool $delete = true): void
    {
        $indexConfig = $this->configManager->getIndexConfiguration($indexName);
        if (!$indexConfig instanceof IndexConfig) {
            throw new \RuntimeException(\sprintf('Incorrect index configuration object. Expecting IndexConfig, but got: %s ', \get_class($indexConfig)));
        }

        if ($indexConfig->isUseAlias()) {
            $index = $this->indexManager->getIndex($indexName);
            $this->aliasProcessor->switchIndexAlias($indexConfig, $index, false, $delete);
        }
    }
}
