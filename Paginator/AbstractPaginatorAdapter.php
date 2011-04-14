<?php

namespace FOQ\ElasticaBundle\Paginator;

use Zend\Paginator\Adapter;
use Elastica_Searchable;
use Elastica_Query;

/**
 * Implements the Zend\Paginator\Adapter Interface for use with Zend\Paginator\Paginator
 *
 * Allows pagination of Elastica_Query. Does not map results
 */
abstract class AbstractPaginatorAdapter implements Adapter
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
     * @see Zend\Paginator\Adapter::count
     */
    public function count()
    {
		return $this->searchable->count($this->query);
    }
}
