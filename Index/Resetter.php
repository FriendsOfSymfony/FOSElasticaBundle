<?php

namespace FOS\ElasticaBundle\Index;

use Elastica\Index;
use Elastica\Exception\ResponseException;
use Elastica\Type\Mapping;
use FOS\ElasticaBundle\Configuration\ConfigManager;
use FOS\ElasticaBundle\Event\ResetEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Deletes and recreates indexes
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
     * @var IndexManager
     */
    private $indexManager;

    /**
     * @var MappingBuilder
     */
    private $mappingBuilder;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

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
        $this->aliasProcessor  = $aliasProcessor;
        $this->configManager   = $configManager;
        $this->indexManager    = $indexManager;
        $this->mappingBuilder  = $mappingBuilder;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Deletes and recreates all indexes
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
     * @param bool $populating
     * @param bool $force If index exists with same name as alias, remove it
     * @throws \InvalidArgumentException if no index exists for the given name
     */
    public function resetIndex($indexName, $populating = false, $force = false)
    {
        $event = new ResetEvent($indexName, null, $populating, $force);
        $this->eventDispatcher->dispatch(ResetEvent::PRE_INDEX_RESET, $event);

        $indexConfig = $this->configManager->getIndexConfiguration($indexName);
        $index = $this->indexManager->getIndex($indexName);

        if ($indexConfig->isUseAlias()) {
            $this->aliasProcessor->setRootName($indexConfig, $index);
        }

        $mapping = $this->mappingBuilder->buildIndexMapping($indexConfig);
        $index->create($mapping, true);

        if (!$populating and $indexConfig->isUseAlias()) {
            $this->aliasProcessor->switchIndexAlias($indexConfig, $index, $force);
        }

        $this->eventDispatcher->dispatch(ResetEvent::POST_INDEX_RESET, $event);
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
        $event = new ResetEvent($indexName, $typeName);
        $this->eventDispatcher->dispatch(ResetEvent::PRE_TYPE_RESET, $event);

        $typeConfig = $this->configManager->getTypeConfiguration($indexName, $typeName);
        $type = $this->indexManager->getIndex($indexName)->getType($typeName);

        try {
            $type->delete();
        } catch (ResponseException $e) {
            if (strpos($e->getMessage(), 'TypeMissingException') === false) {
                throw $e;
            }
        }

        $mapping = new Mapping;
        foreach ($this->mappingBuilder->buildTypeMapping($typeConfig) as $name => $field) {
            $mapping->setParam($name, $field);
        }

        $type->setMapping($mapping);

        $this->eventDispatcher->dispatch(ResetEvent::POST_TYPE_RESET, $event);
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
