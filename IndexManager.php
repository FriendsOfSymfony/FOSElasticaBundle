<?php

namespace FOQ\ElasticaBundle;

class IndexManager
{
    protected $indexes;

    public function __construct(array $indexes)
    {
        $this->indexes = $indexes;
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
     * Destroys and creates all registered indexes
     *
     * @return null
     */
    public function createAllIndexes()
    {
        foreach ($this->getAllIndexes() as $index) {
            $index->create();
        }
    }
}
