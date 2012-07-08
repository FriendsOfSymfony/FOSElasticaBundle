<?php

namespace FOQ\ElasticaBundle\Paginator;

use FOQ\ElasticaBundle\Paginator\PartialResultsInterface;
use Elastica_ResultSet;

/**
 * Raw partial results transforms to a simple array
 */
class RawPartialResults implements PartialResultsInterface
{
    protected $resultSet;

    /**
     * @param \Elastica_ResultSet $resultSet
     */
    public function __construct(Elastica_ResultSet $resultSet)
    {
        $this->resultSet = $resultSet;
    }

    /**
     * {@inheritDoc}
     */
    public function toArray()
    {
        return array_map(function($result) {
            return $result->getSource();
        }, $this->resultSet->getResults());
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalHits()
    {
        return $this->resultSet->getTotalHits();
    }

    /**
     * {@inheritDoc}
     */
    public function getFacets()
    {
        if ($this->resultSet->hasFacets()) {
            return $this->resultSet->getFacets();
        }

        return null;
    }
}