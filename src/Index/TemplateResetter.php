<?php
namespace FOS\ElasticaBundle\Index;

use FOS\ElasticaBundle\Configuration\ManagerInterface;
use Elastica\Client;
use Elastica\Request;
use FOS\ElasticaBundle\Configuration\IndexTemplateConfig;

/**
 * Class Template resetter
 *
 * @author Dmitry Balabka <dmitry.balabka@intexsys.lv>
 */
class TemplateResetter implements ResetterInterface
{
    /***
     * @var ManagerInterface
     */
    private $configManager;

    /**
     * @var MappingBuilder
     */
    private $mappingBuilder;

    /**
     * @var Client
     */
    private $client;

    /**
     * Index template manager
     *
     * @var IndexTemplateManager
     */
    private $indexTemplateManager;

    public function __construct(
        ManagerInterface $configManager,
        MappingBuilder $mappingBuilder,
        Client $client,
        IndexTemplateManager $indexTemplateManager
    ) {
        $this->configManager = $configManager;
        $this->mappingBuilder = $mappingBuilder;
        $this->client = $client;
        $this->indexTemplateManager = $indexTemplateManager;
    }

    public function resetAllIndexes($deleteIndexes = false)
    {
        foreach ($this->configManager->getIndexNames() as $name) {
            $this->resetIndex($name, $deleteIndexes);
        }
    }

    public function resetIndex($indexName, $deleteIndexes = false)
    {
        $indexTemplateConfig = $this->configManager->getIndexConfiguration($indexName);
        if (!$indexTemplateConfig instanceof IndexTemplateConfig) {
            throw new \RuntimeException(
                \sprintf('Incorrect index configuration object. Expecting IndexTemplateConfig, but got: %s ', \get_class($indexTemplateConfig))
            );
        }
        $indexTemplate = $this->indexTemplateManager->getIndexTemplate($indexName);

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
}
