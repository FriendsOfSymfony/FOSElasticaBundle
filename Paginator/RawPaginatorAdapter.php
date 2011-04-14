<?php

namespace FOQ\ElasticaBundle\Paginator;

/**
 * Implements the Zend\Paginator\Adapter Interface for use with Zend\Paginator\Paginator
 *
 * Allows pagination of Elastica_Query. Does not map results
 */
class RawPaginatorAdapter extends AbstractPaginatorAdapter
{
    /**
     * @see Zend\Paginator\Adapter::getItems
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $results = $this->getElasticaResults($offset, $itemCountPerPage);

        return array_map(function($result) { return $result->getSource(); }, $results);
    }
}
