<?php

namespace FOQ\ElasticaBundle\Paginator;

use FOQ\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface;
use FOQ\ElasticaBundle\Paginator\RawPartialResults;
use Elastica_ResultSet;

/**
 * Partial transformed result set
 */
class TransformedPartialResults extends RawPartialResults
{
    protected $transformer;

    /**
     * @param \Elastica_ResultSet $resultSet
     * @param \FOQ\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface $transformer
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