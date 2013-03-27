<?php

namespace FOS\ElasticaBundle\Paginator;

/**
 * Implements the Pagerfanta\Adapter\AdapterInterface for use with Zend\Paginator\Paginator
 *
 * Allows pagination of Elastica_Query. Does not map results
 */
class RawPaginatorAdapter extends AbstractPaginatorAdapter
{
    /**
     * @see Pagerfanta\Adapter\AdapterInterface::getSlice
     */
    public function getSlice($offset, $length)
    {
        $results = $this->getElasticaResults($offset, $length);

        return array_map(function($result) { return $result->getSource(); }, $results);
    }
}
