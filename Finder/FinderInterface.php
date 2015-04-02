<?php

namespace FOS\ElasticaBundle\Finder;

interface FinderInterface
{
    /**
     * Searches for query results within a given limit.
     *
     * @param mixed $query   Can be a string, an array or an \Elastica\Query object
     * @param int   $limit   How many results to get
     * @param array $options
     * @param array $transformOptions Transform options
     *
     * @return array results
     */
    public function find($query, $limit = null, $options = array(), $transformOptions = array());
}
