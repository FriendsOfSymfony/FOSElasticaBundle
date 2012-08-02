<?php

namespace FOQ\ElasticaBundle\Finder;

use FOQ\ElasticaBundle\Paginator\PaginatorAdapterInterface;
use Pagerfanta\Pagerfanta;
use Elastica_Query;

interface PaginatedFinderInterface extends FinderInterface
{
    /**
     * Searches for query results and returns them wrapped in a paginator
     *
     * @param mixed $query  Can be a string, an array or an Elastica_Query object
     * @return Pagerfanta paginated results
     */
    function findPaginated($query);

    /**
     * Creates a paginator adapter for this query
     *
     * @param mixed $query
     * @return PaginatorAdapterInterface
     */
    function createPaginatorAdapter($query);
}
