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
     */
    public function getNbResults()
    {
        return $this->adapter->getTotalHits();
    }

    /**
     * Returns Aggregations.
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
     */
    public function getSlice($offset, $length)
    {
        return $this->adapter->getResults($offset, $length)->toArray();
    }
}
