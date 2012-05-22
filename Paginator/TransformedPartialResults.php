<?php

namespace FOQ\ElasticaBundle\Paginator;

use FOQ\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface;
use FOQ\ElasticaBundle\Paginator\PartialResultsInterface;
use Elastica_ResultSet;

class TransformedPartialResults implements PartialResultsInterface
{
    private $transformer;
    private $resultSet;

    /**
     * @param \Elastica_ResultSet $resultSet
     * @param \FOQ\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface $transformer
     */
    public function __construct(Elastica_ResultSet $resultSet,ElasticaToModelTransformerInterface $transformer)
    {
        $this->transformer = $transformer;
        $this->resultSet = $resultSet;
    }

    /**
     * {@inheritDoc}
     */
    public function toArray()
    {
        return $this->transformer->transform($this->resultSet);
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalHits()
    {
        return $this->resultSet->getTotalHits();
    }
}