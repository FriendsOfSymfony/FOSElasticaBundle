<?php

/**
 * This file is part of the FOSElasticaBundle project.
 *
 * (c) Tim Nagel <tim@nagel.com.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Configuration;

/**
 * Central manager for index and type configuration.
 */
class ConfigManager implements ManagerInterface
{
    /**
     * @var IndexConfig[]
     */
    private $indexes = array();

    /**
     * @var IndexTemplateConfig[]
     */
    private $indexTemplates = array();

    /**
     * @param Source\SourceInterface[] $indexSources
     * @param Source\SourceInterface[] $indexTemplateSources
     */
    public function __construct(array $indexSources, array $indexTemplateSources)
    {
        foreach ($indexSources as $source) {
            $this->indexes = array_merge($source->getConfiguration(), $this->indexes);
        }
        foreach ($indexTemplateSources as $source) {
            $this->indexTemplates = array_merge($source->getConfiguration(), $this->indexTemplates);
        }
    }

    public function getIndexConfiguration($indexName)
    {
        if (!$this->hasIndexConfiguration($indexName)) {
            throw new \InvalidArgumentException(sprintf('Index with name "%s" is not configured.', $indexName));
        }

        return $this->indexes[$indexName];
    }

    /**
     * @param string $indexTemplateName
     *
     * @return IndexTemplateConfig
     */
    public function getIndexTemplateConfiguration($indexTemplateName)
    {
        if (!$this->hasIndexTemplateConfiguration($indexTemplateName)) {
            throw new \InvalidArgumentException(sprintf('Index template with name "%s" is not configured.', $indexTemplateName));
        }

        return $this->indexTemplates[$indexTemplateName];
    }

    public function getIndexNames()
    {
        return array_keys($this->indexes);
    }

    public function getIndexTemplatesNames()
    {
        return array_keys($this->indexTemplates);
    }

    public function getTypeConfiguration($indexName, $typeName)
    {
        $index = $this->getIndexConfiguration($indexName);
        $type = $index->getType($typeName);

        if (!$type) {
            throw new \InvalidArgumentException(sprintf('Type with name "%s" on index "%s" is not configured', $typeName, $indexName));
        }

        return $type;
    }

    public function hasIndexConfiguration($indexName)
    {
        return isset($this->indexes[$indexName]);
    }

    public function hasIndexTemplateConfiguration($indexTemplateName)
    {
        return isset($this->indexTemplates[$indexTemplateName]);
    }
}
