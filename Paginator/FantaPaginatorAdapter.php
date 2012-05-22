<?php

namespace FOQ\ElasticaBundle\Paginator;

use Pagerfanta\Adapter\AdapterInterface;
use FOQ\ElasticaBundle\Paginator\PaginatorAdapterInterface;

class FantaPaginatorAdapter implements AdapterInterface
{
    private $adapter;

    /**
     * @param PaginatorAdapterInterface $adapter
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
     * Returns an slice of the results.
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
        return $this->adapter->getResults($offset,$length)->toArray();
    }
}
