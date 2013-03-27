<?php

namespace FOS\ElasticaBundle\Paginator;

use FOS\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface;
use FOS\ElasticaBundle\Paginator\TransformedPartialResults;
use Elastica_Searchable;
use Elastica_Query;

/**
 * Allows pagination of Elastica_Query
 */
class TransformedPaginatorAdapter extends RawPaginatorAdapter
{
    private $transformer;

    /**
     * @param Elastica_SearchableInterface the object to search in
     * @param Elastica_Query the query to search
     * @param ElasticaToModelTransformerInterface the transformer for fetching the results
     */
    public function __construct(Elastica_Searchable $searchable, Elastica_Query $query, ElasticaToModelTransformerInterface $transformer)
    {
        parent::__construct($searchable, $query);

        $this->transformer = $transformer;
    }

    /**
     * {@inheritDoc}
     */
    public function getResults($offset, $length)
    {
        return new TransformedPartialResults($this->getElasticaResults($offset, $length), $this->transformer);
    }
}
