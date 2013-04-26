<?php

namespace FOS\ElasticaBundle\Finder;

use FOS\ElasticaBundle\Paginator\PaginatorAdapterInterface;
use Pagerfanta\Pagerfanta;
use Elastica\Query;

interface PaginatedFinderInterface extends FinderInterface
{
    /**
     * Searches for query results and returns them wrapped in a paginator
     *
     * @param mixed $query  Can be a string, an array or an \Elastica\Query object
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
