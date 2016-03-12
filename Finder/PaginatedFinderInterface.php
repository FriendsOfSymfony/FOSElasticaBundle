<?php

namespace FOS\ElasticaBundle\Finder;

use FOS\ElasticaBundle\Paginator\PaginatorAdapterInterface;
use Pagerfanta\Pagerfanta;
use Elastica\Query;

interface PaginatedFinderInterface extends FinderInterface
{
    /**
     * Searches for query results and returns them wrapped in a paginator.
     *
     * @param mixed $query   Can be a string, an array or an \Elastica\Query object
     * @param array $options
     *
     * @return Pagerfanta paginated results
     */
    public function findPaginated($query, $options = array());

    /**
     * Creates a paginator adapter for this query.
     *
     * @param mixed $query
     * @param array $options
     *
     * @return PaginatorAdapterInterface
     */
    public function createPaginatorAdapter($query, $options = array());
    
    /**
     * Searches for query results and returns them wrapped in a paginator.
     *
     * @param mixed $query   Can be a string, an array or an \Elastica\Query object
     * @param array $options
     *
     * @return Pagerfanta paginated results
     */
    public function findRawPaginated($query, $options = array());
    
    /**
     * Creates a paginator adapter for this query.
     *
     * @param mixed $query
     * @param array $options
     *
     * @return PaginatorAdapterInterface
     */
    public function createRawPaginatorAdapter($query, $options = array());
    
    /**
     * Searches for query results within a given limit.
     *
     * @param mixed $query   Can be a string, an array or an \Elastica\Query object
     * @param int   $limit   How many results to get
     * @param array $options
     *
     * @return array results
     */
    public function findRawResult($query, $limit = null, $options = array());
}
