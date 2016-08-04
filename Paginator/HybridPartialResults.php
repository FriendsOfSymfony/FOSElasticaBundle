<?php

namespace FOS\ElasticaBundle\Paginator;

use FOS\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface;
use Elastica\ResultSet;

/**
 * Partial transformed result set
 */
class HybridPartialResults extends RawPartialResults
{
    /**
     * @var ElasticaToModelTransformerInterface
     */
    protected $transformer;

    /**
     * @param ResultSet                           $resultSet
     * @param ElasticaToModelTransformerInterface $transformer
     */
    public function __construct(ResultSet $resultSet, ElasticaToModelTransformerInterface $transformer)
    {
        parent::__construct($resultSet);

        $this->transformer = $transformer;
    }

    /**
     * {@inheritDoc}
     */
    public function toArray()
    {
        return $this->transformer->hybridTransform($this->resultSet->getResults());
    }
}
