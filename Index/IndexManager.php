<?php

namespace FOS\ElasticaBundle\Index;

use Elastica\IndexTemplate;
use FOS\ElasticaBundle\Elastica\Index;

class IndexManager
{
    /**
     * @var Index
     */
    private $defaultIndex;

    /**
     * @var array
     */
    private $indexes;

    /**
     * @var IndexTemplate[]
     */
    private $indexTemplates;

    /**
     * @param array $indexes
     * @param Index $defaultIndex
     * @param array $templates
     */
    public function __construct(array $indexes, Index $defaultIndex, array $templates = array())
    {
        $this->defaultIndex = $defaultIndex;
        $this->indexes = $indexes;
        $this->indexTemplates = $templates;
    }

    /**
     * Gets all registered indexes.
     *
     * @return array
     */
    public function getAllIndexes()
    {
        return $this->indexes;
    }

    /**
     * Gets an index by its name.
     *
     * @param string $name Index to return, or the default index if null
     *
     * @return Index
     *
     * @throws \InvalidArgumentException if no index exists for the given name
     */
    public function getIndex($name = null)
    {
        if (null === $name) {
            return $this->defaultIndex;
        }

        if (!isset($this->indexes[$name])) {
            throw new \InvalidArgumentException(sprintf('The index "%s" does not exist', $name));
        }

        return $this->indexes[$name];
    }

    /**
     * Gets an index template by its name.
     *
     * @param string $name Index template to return
     *
     * @return IndexTemplate
     *
     * @throws \InvalidArgumentException if no index template exists for the given name
     */
    public function getIndexTemplate($name = null)
    {
        if (!isset($this->indexTemplates[$name])) {
            throw new \InvalidArgumentException(sprintf('The index template "%s" does not exist', $name));
        }

        return $this->indexTemplates[$name];
    }

    /**
     * Gets the default index.
     *
     * @return Index
     */
    public function getDefaultIndex()
    {
        return $this->defaultIndex;
    }
}
