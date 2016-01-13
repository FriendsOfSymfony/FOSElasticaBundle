<?php

namespace FOS\ElasticaBundle\Index;

use Elastica\Index;
use Elastica\Exception\ResponseException;
use Elastica\Type\Mapping;
use FOS\ElasticaBundle\Configuration\ConfigManager;
use FOS\ElasticaBundle\Event\IndexResetEvent;
use FOS\ElasticaBundle\Event\TypeResetEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Deletes and recreates indexes.
 */
class Resetter
{
    /**
     * @var AliasProcessor
     */
    private $aliasProcessor;

    /***
     * @var ConfigManager
     */
    private $configManager;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var IndexManager
     */
    private $indexManager;

    /**
     * @var MappingBuilder
     */
    private $mappingBuilder;

    /**
     * @param ConfigManager            $configManager
     * @param IndexManager             $indexManager
     * @param AliasProcessor           $aliasProcessor
     * @param MappingBuilder           $mappingBuilder
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        ConfigManager $configManager,
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
     *
     * @param bool $populating
     * @param bool $force
     */
    public function resetAllIndexes($populating = false, $force = false)
    {
        foreach ($this->configManager->getIndexNames() as $name) {
            $this->resetIndex($name, $populating, $force);
        }
    }

    /**
     * Deletes and recreates the named index. If populating, creates a new index
     * with a randomised name for an alias to be set after population.
     *
     * @param string $indexName
     * @param bool   $populating
     * @param bool   $force      If index exists with same name as alias, remove it
     *
     * @throws \InvalidArgumentException if no index exists for the given name
     */
    public function resetIndex($indexName, $populating = false, $force = false)
    {
        $indexConfig = $this->configManager->getIndexConfiguration($indexName);
        $index = $this->indexManager->getIndex($indexName);

        $event = new IndexResetEvent($indexName, $populating, $force);
        $this->dispatcher->dispatch(IndexResetEvent::PRE_INDEX_RESET, $event);

        if ($indexConfig->isUseAlias()) {
            $this->aliasProcessor->setRootName($indexConfig, $index);
        }
        $mapping = $this->mappingBuilder->buildIndexMapping($indexConfig);

        $index->create($mapping, true);

        if (!$populating and $indexConfig->isUseAlias()) {
            $this->aliasProcessor->switchIndexAlias($indexConfig, $index, $force);
        }

        $this->dispatcher->dispatch(IndexResetEvent::POST_INDEX_RESET, $event);
    }

    /**
     * A command run when a population has finished.
     *
     * @param string $indexName
     */
    public function postPopulate($indexName)
    {
        $indexConfig = $this->configManager->getIndexConfiguration($indexName);

        if ($indexConfig->isUseAlias()) {
            $index = $this->indexManager->getIndex($indexName);
            $this->aliasProcessor->switchIndexAlias($indexConfig, $index);
        }
    }
}
