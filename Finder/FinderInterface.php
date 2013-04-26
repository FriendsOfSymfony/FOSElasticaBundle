<?php

namespace FOS\ElasticaBundle\Finder;

interface FinderInterface
{
    /**
     * Searches for query results within a given limit
     *
     * @param mixed $query  Can be a string, an array or an \Elastica\Query object
     * @param int $limit How many results to get
     * @return array results
     */
    function find($query, $limit = null);
}
