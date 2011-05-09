<?php

namespace FOQ\ElasticaBundle\Doctrine;

use FOQ\ElasticaBundle\Paginator\AbstractPaginatorAdapter;
use FOQ\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface;
use Elastica_Searchable;
use Elastica_Query;

/**
 * Implements the Zend\Paginator\Adapter Interface for use with Zend\Paginator\Paginator
 *
 * Allows pagination of Elastica_Query
 */
class PaginatorAdapter extends AbstractPaginatorAdapter
{
    protected $transformer;

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
     * @see Zend\Paginator\Adapter::getItems
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $results = $this->getElasticaResults($offset, $itemCountPerPage);

        return $this->transformer->transform($results);
    }
}
