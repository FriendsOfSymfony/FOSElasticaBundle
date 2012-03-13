<?php

namespace FOQ\ElasticaBundle;

class IndexManager
{
    protected $indexesByName;
    protected $defaultIndexName;

    /**
     * Constructor.
     *
     * @param array  $indexesByName
     * @param string $defaultIndexName
     */
    public function __construct(array $indexesByName, $defaultIndexName)
    {
        $this->indexesByName = $indexesByName;
        $this->defaultIndexName = $defaultIndexName;
    }

    /**
     * Gets all registered indexes
     *
     * @return array
     */
    public function getAllIndexes()
    {
        return $this->indexesByName;
    }

    /**
     * Gets an index by its name
     *
     * @param string $name Index to return, or the default index if null
     * @return Elastica_Index
     * @throws InvalidArgumentException if no index exists for the given name
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
     * @return Elastica_Index
     */
    public function getDefaultIndex()
    {
        return $this->getIndex($this->defaultIndexName);
    }
}
