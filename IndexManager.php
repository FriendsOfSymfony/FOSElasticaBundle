<?php

namespace FOS\ElasticaBundle;

class IndexManager
{
    protected $indexConfigs;
    protected $defaultIndexKey;

    /**
     * Constructor.
     *
     * @param array  $indexConfigs    Indexes configuration
     * @param string $defaultIndexKey Config key of default index
     */
    public function __construct(array $indexConfigs, $defaultIndexKey)
    {
        $this->indexConfigs = $indexConfigs;
        $this->defaultIndexKey = $defaultIndexKey;
    }

    /**
     * Gets all registered indexes
     *
     * @return array
     */
    public function getAllIndexes()
    {
        return array_keys($this->indexConfigs);
    }

    /**
     * Gets an index by its name
     *
     * @param string|null $key Index key
     *
     * @return ElasticaDynamicIndex
     *
     * @throws \InvalidArgumentException if no index config exists for the given name
     */
    public function getIndex($key = null)
    {
        $config = $this->getIndexConfig($key);
        return isset($config['index']) ? $config['index'] : $this->buildIndex($key);
    }

    /**
     * Factory method for creating index instances
     *
     * @param string|null $key Index key
     *
     * @return ElasticaDynamicIndex
     *
     * @throws \InvalidArgumentException if no index config exists for the given name
     */
    public function buildIndex($key = null)
    {
        $config = $this->getIndexConfig($key);
        $esIndex = new ElasticaDynamicIndex($config['client'], $config['name_or_alias']);
        $this->indexConfigs[$key ? : $this->defaultIndexKey]['index'] = $esIndex;

        return $esIndex;
    }

    /**
     * Returns index configuration by key
     *
     * @param string|null $key Index key
     *
     * @return array
     *
     * @throws \InvalidArgumentException if no index config exists for the given name
     */
    public function getIndexConfig($key)
    {
        if (null === $key) {
            $key = $this->defaultIndexKey;
        }

        if (!isset($this->indexConfigs[$key])) {
            throw new \InvalidArgumentException(sprintf('The index "%s" does not exist', $key));
        }

        return $this->indexConfigs[$key];
    }

    /**
     * Gets the default index
     *
     * @return Index
     */
    public function getDefaultIndex()
    {
        return $this->getIndex($this->defaultIndexKey);
    }
}
