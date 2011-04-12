<?php

namespace FOQ\ElasticaBundle;

/**
 * Deletes and recreates indexes
 **/
class Reseter
{
    protected $indexManager;

    public function __construct(IndexManager $indexManager)
    {
        $this->indexManager = $indexManager;
    }

    /**
     * Resets all indexes
     *
     * @return null
     **/
    public function reset()
    {
        foreach ($this->indexManager->getAllIndexes() as $index) {
            $index->delete();
            $index->create();
        }
    }
}
