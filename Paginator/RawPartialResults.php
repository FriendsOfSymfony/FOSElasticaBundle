<?php

namespace FOQ\ElasticaBundle\Paginator;

use FOQ\ElasticaBundle\Paginator\PartialResultsInterface;
use Elastica_ResultSet;

class RawPartialResults implements PartialResultsInterface
{
    private $resultSet;

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
        return array_map(function($result) { return $result->getSource(); }, $this->resultSet->getResults());
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalHits()
    {
        return $this->resultSet->getTotalHits();
    }
}