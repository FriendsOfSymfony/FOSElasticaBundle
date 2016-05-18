<?php

namespace FOS\ElasticaBundle\Index;

use Elastica\Index;
use Elastica\Exception\ResponseException;
use Elastica\Type\Mapping;
use FOS\ElasticaBundle\Configuration\ConfigManager;
use Elastica\Client;
use Elastica\Request;
use FOS\ElasticaBundle\Configuration\IndexTemplateConfig;
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
     * @var Client
     */
    private $client;

    /**
     * @param ConfigManager $configManager
     * @param IndexManager $indexManager
     * @param AliasProcessor $aliasProcessor
     * @param MappingBuilder $mappingBuilder
     * @param EventDispatcherInterface $eventDispatcher
     * @param Client $client
     */
    public function __construct(
        ConfigManager $configManager,
        IndexManager $indexManager,
        AliasProcessor $aliasProcessor,
        MappingBuilder $mappingBuilder,
        EventDispatcherInterface $eventDispatcher,
        Client $client
    ) {
        $this->aliasProcessor = $aliasProcessor;
        $this->configManager = $configManager;
        $this->dispatcher = $eventDispatcher;
        $this->indexManager = $indexManager;
        $this->mappingBuilder = $mappingBuilder;
        $this->client = $client;
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
     * Deletes and recreates a mapping type for the named index.
     *
     * @param string $indexName
     * @param string $typeName
     *
     * @throws \InvalidArgumentException if no index or type mapping exists for the given names
     * @throws ResponseException
     */
    public function resetIndexType($indexName, $typeName)
    {
        $typeConfig = $this->configManager->getTypeConfiguration($indexName, $typeName);
        $index = $this->indexManager->getIndex($indexName);
        $type = $index->getType($typeName);

        $indexConfig = $this->configManager->getIndexConfiguration($indexName);
        $settings = $indexConfig->getSettings();

        $event = new TypeResetEvent($indexName, $typeName);
        $this->dispatcher->dispatch(TypeResetEvent::PRE_TYPE_RESET, $event);

        try {
            $type->delete();
        } catch (ResponseException $e) {
            if (strpos($e->getMessage(), 'TypeMissingException') === false) {
                throw $e;
            }
        }

        if (!empty($settings)) {
            $index->close();
            $index->setSettings($settings);
            $index->open();
        }

        $mapping = new Mapping();
        foreach ($this->mappingBuilder->buildTypeMapping($typeConfig) as $name => $field) {
            $mapping->setParam($name, $field);
        }

        $type->setMapping($mapping);

        $this->dispatcher->dispatch(TypeResetEvent::POST_TYPE_RESET, $event);
    }

    public function resetAllTemplates($deleteIndexes = false)
    {
        foreach ($this->configManager->getIndexTemplatesNames() as $name) {
            $this->resetTemplate($name, $deleteIndexes);
        }
    }

    public function resetTemplate($indexTemplateName, $deleteIndexes = false)
    {
        $indexTemplateConfig = $this->configManager->getIndexTemplateConfiguration($indexTemplateName);
        $indexTemplate = $this->indexManager->getIndexTemplate($indexTemplateName);

        $mapping = $this->mappingBuilder->buildIndexTemplateMapping($indexTemplateConfig);
        if ($deleteIndexes) {
            $this->deleteTemplateIndexes($indexTemplateConfig);
        }
        $indexTemplate->create($mapping);
    }

    /**
     * Delete all template indexes
     *
     * @param IndexTemplateConfig $template
     */
    public function deleteTemplateIndexes(IndexTemplateConfig $template)
    {
        $this->client->request($template->getTemplate() . '/', Request::DELETE);
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
