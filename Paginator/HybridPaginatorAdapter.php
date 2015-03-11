<?php

namespace FOS\ElasticaBundle\Paginator;

use FOS\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface;
use Elastica\SearchableInterface;
use Elastica\Query;

/**
 * Allows pagination of \Elastica\Query
 */
class HybridPaginatorAdapter extends RawPaginatorAdapter
{
    private $transformer;

    /**
     * @param SearchableInterface $searchable the object to search in
     * @param Query $query the query to search
     * @param ElasticaToModelTransformerInterface $transformer the transformer for fetching the results
     */
    public function __construct(SearchableInterface $searchable, Query $query, ElasticaToModelTransformerInterface $transformer)
    {
        parent::__construct($searchable, $query);

        $this->transformer = $transformer;
    }

    /**
     * {@inheritDoc}
     */
    public function getResults($offset, $length)
    {
        return new HybridPartialResults($this->getElasticaResults($offset, $length), $this->transformer);
    }
}
