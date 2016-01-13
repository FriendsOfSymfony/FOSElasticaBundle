<?php

namespace FOS\ElasticaBundle\Paginator;

use Elastica\ResultSet;
use Elastica\Result;

/**
 * Raw partial results transforms to a simple array.
 */
class RawPartialResults implements PartialResultsInterface
{
    protected $resultSet;

    /**
     * @param ResultSet $resultSet
     */
    public function __construct(ResultSet $resultSet)
    {
        $this->resultSet = $resultSet;
    }

    /**
     * {@inheritDoc}
     */
    public function toArray()
    {
        return array_map(function (Result $result) {
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
     * @deprecated remvem from rufflin api 3.0.1
     */
    public function getFacets()
    {
        return;
    }

    /**
     * {@inheritDoc}
     */
    public function getAggregations()
    {
        if ($this->resultSet->hasAggregations()) {
            return $this->resultSet->getAggregations();
        }

        return;
    }
}
