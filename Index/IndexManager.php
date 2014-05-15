<?php

namespace FOS\ElasticaBundle\Index;

use FOS\ElasticaBundle\Elastica\TransformingIndex;

class IndexManager
{
    /**
     * @var TransformingIndex[]
     */
    protected $indexesByName;

    /**
     * @var string
     */
    protected $defaultIndexName;

    /**
     * @param TransformingIndex[] $indexesByName
     * @param TransformingIndex $defaultIndex
     */
    public function __construct(array $indexesByName, TransformingIndex $defaultIndex)
    {
        $this->indexesByName = $indexesByName;
        $this->defaultIndexName = $defaultIndex->getName();
    }

    /**
     * Gets all registered indexes
     *
     * @return TransformingIndex[]
     */
    public function getAllIndexes()
    {
        return $this->indexesByName;
    }

    /**
     * Gets an index by its name
     *
     * @param string $name Index to return, or the default index if null
     * @return TransformingIndex
     * @throws \InvalidArgumentException if no index exists for the given name
     */
    public function getIndex($name = null)
    {
        if (null === $name) {
            $name = $this->defaultIndexName;
        }

        if (!isset($this->indexesByName[$name])) {
            throw new \InvalidArgumentException(sprintf('The index "%s" does not exist', $name));
        }

        return $this->indexesByName[$name];
    }

    /**
     * Gets the default index
     *
     * @return TransformingIndex
     */
    public function getDefaultIndex()
    {
        return $this->getIndex($this->defaultIndexName);
    }
}
