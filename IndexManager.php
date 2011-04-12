<?php

namespace FOQ\ElasticaBundle;

use InvalidArgumentException;

class IndexManager
{
    protected $indexes;
    protected $defaultIndex;

    public function __construct(array $indexes, $defaultIndex)
    {
        $this->indexes      = $indexes;
        $this->defaultIndex = $defaultIndex;
    }

    /**
     * Gets all registered indexes
     *
     * @return array
     */
    public function getAllIndexes()
    {
        return $this->indexes;
    }

    /**
     * Gets an index by its name
     *
     * @return Elastica_Index
     **/
    public function getIndex($name)
    {
        if (!$name) {
            return $this->getDefaultIndex();
        }
        if (!isset($this->indexes[$name])) {
            throw new InvalidArgumentException(sprintf('The index "%s" does not exist', $name));
        }

        return $this->indexes[$name];
    }

    /**
     * Gets the default index
     *
     * @return Elastica_Index
     **/
    public function getDefaultIndex()
    {
        return $this->defaultIndex;
    }
}
