<?php

namespace FOS\ElasticaBundle\Paginator;

use FOS\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface;
use FOS\ElasticaBundle\Paginator\RawPartialResults;
use Elastica_ResultSet;

/**
 * Partial transformed result set
 */
class TransformedPartialResults extends RawPartialResults
{
    protected $transformer;

    /**
     * @param \Elastica_ResultSet $resultSet
     * @param \FOS\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface $transformer
     */
    public function __construct(Elastica_ResultSet $resultSet, ElasticaToModelTransformerInterface $transformer)
    {
        parent::__construct($resultSet);

        $this->transformer = $transformer;
    }

    /**
     * {@inheritDoc}
     */
    public function toArray()
    {
        return $this->transformer->transform($this->resultSet->getResults());
    }
}