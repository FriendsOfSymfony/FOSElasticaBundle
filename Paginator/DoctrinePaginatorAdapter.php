<?php

namespace FOQ\ElasticaBundle\Paginator;

use FOQ\ElasticaBundle\MapperInterface;
use Elastica_Searchable;
use Elastica_Query;

/**
 * Implements the Zend\Paginator\Adapter Interface for use with Zend\Paginator\Paginator
 *
 * Allows pagination of Elastica_Query
 */
class DoctrinePaginatorAdapter extends AbstractPaginatorAdapter
{
    protected $mapper;

    /**
     * @param Elastica_SearchableInterface the object to search in
     * @param Elastica_Query the query to search
     * @param MapperInterface the mapper for fetching the results
     */
    public function __construct(Elastica_Searchable $searchable, Elastica_Query $query, MapperInterface $mapper)
    {
        parent::__construct($searchable, $query);
        $this->mapper = $mapper;
    }

    /**
     * @see Zend\Paginator\Adapter::getItems
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $results = $this->getElasticaResults($offset, $itemCountPerPage);

        return $this->mapper->fromElasticaObjects($results);
    }
}
