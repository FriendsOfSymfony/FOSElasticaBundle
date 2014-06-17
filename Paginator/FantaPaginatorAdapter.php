<?php

namespace FOS\ElasticaBundle\Paginator;

use Pagerfanta\Adapter\AdapterInterface;

class FantaPaginatorAdapter implements AdapterInterface
{
    private $adapter;

    /**
     * @param \FOS\ElasticaBundle\Paginator\PaginatorAdapterInterface $adapter
     */
    public function __construct(PaginatorAdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Returns the number of results.
     *
     * @return integer The number of results.
     *
     * @api
     */
    public function getNbResults()
    {
        return $this->adapter->getTotalHits();
    }

    /**
     * Returns Facets
     *
     * @return mixed
     *
     * @api
     */
    public function getFacets()
    {
        return $this->adapter->getFacets();
    }
    
    /**
     * Returns Buckets
     *
     * @return mixed
     *
     * @api
     */
    public function getAggregations()
    {
        return $this->adapter->getAggregations();
    }

    /**
     * Returns a slice of the results.
     *
     * @param integer $offset The offset.
     * @param integer $length The length.
     *
     * @return array|\Traversable The slice.
     *
     * @api
     */
    public function getSlice($offset, $length)
    {
        return $this->adapter->getResults($offset, $length)->toArray();
    }
}
