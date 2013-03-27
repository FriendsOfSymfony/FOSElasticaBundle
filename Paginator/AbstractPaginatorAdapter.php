<?php

namespace FOS\ElasticaBundle\Paginator;

use Pagerfanta\Adapter\AdapterInterface;
use Elastica_Searchable;
use Elastica_Query;

/**
 * Implements the Pagerfanta\Adapter\AdapterInterface for use with Pagerfanta\Pagerfanta
 *
 * Allows pagination of Elastica_Query. Does not map results
 */
abstract class AbstractPaginatorAdapter implements AdapterInterface
{
    /**
     * @var Elastica_SearchableInterface the object to search in
     */
    protected $searchable = null;

    /**
     * @var Elastica_Query the query to search
     */
    protected $query = null;

    /**
     * @see PaginatorAdapterInterface::__construct
     *
     * @param Elastica_SearchableInterface the object to search in
     * @param Elastica_Query the query to search
     */
    public function __construct(Elastica_Searchable $searchable, Elastica_Query $query)
    {
        $this->searchable = $searchable;
        $this->query      = $query;
    }

    protected function getElasticaResults($offset, $itemCountPerPage)
    {
        $query = clone $this->query;
        $query->setFrom($offset);
        $query->setLimit($itemCountPerPage);

        return $this->searchable->search($query)->getResults();
    }

    /**
     * @see Pagerfanta\Adapter\AdapterInterface::getNbResults
     */
    public function getNbResults()
    {
		return $this->searchable->count($this->query);
    }
}
