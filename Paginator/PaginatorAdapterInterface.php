<?php

namespace FOS\ElasticaBundle\Paginator;

interface PaginatorAdapterInterface
{
    /**
     * Returns the number of results.
     *
     * @return integer The number of results.
     *
     * @api
     */
    function getTotalHits();

    /**
     * Returns an slice of the results.
     *
     * @param integer $offset The offset.
     * @param integer $length The length.
     *
     * @return PartialResultsInterface
     *
     * @api
     */
    function getResults($offset, $length);

    /**
     * Returns Facets
     *
     * @return mixed
     */
    function getFacets();
    
    /**
     * Returns Aggregations
     *
     * @return mixed
     */
    function getAggregations();
}
