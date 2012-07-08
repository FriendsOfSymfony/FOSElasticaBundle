<?php

namespace FOQ\ElasticaBundle\Paginator;

interface PartialResultsInterface
{
    /**
     * Returns the paginated results.
     *
     * @return array
     *
     * @api
     */
    function toArray();

    /**
     * Returns the number of results.
     *
     * @return integer The number of results.
     *
     * @api
     */
    function getTotalHits();

    /**
     * Returns the facets
     *
     * @return array
     */
    function getFacets();
}