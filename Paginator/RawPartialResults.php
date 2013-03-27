<?php

namespace FOS\ElasticaBundle\Paginator;

use FOS\ElasticaBundle\Paginator\PartialResultsInterface;
use Elastica_ResultSet;
use Elastica_Result;

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
        return array_map(function(Elastica_Result $result) {
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