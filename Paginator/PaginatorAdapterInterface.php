<?php

namespace FOS\ElasticaBundle\Paginator;

use FOS\ElasticaBundle\Paginator\PartialResultsInterface;

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
}
