<?php

namespace FOQ\ElasticaBundle;

use Elastica_Exception_Response;

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
            try {
                $index->delete();
            } catch (Elastica_Exception_Response $e) {
                // The index does not exist
            }
            $index->create();
        }
    }
}
