<?php

namespace FOS\ElasticaBundle\Paginator;

trait LegacyFantaPaginatorAdapterTrait
{
    /**
     * Returns a slice of the results.
     *
     * @param int $offset The offset
     * @param int $length The length
     *
     * @return iterable The slice
     */
    public function getSlice($offset, $length)
    {
        return $this->adapter->getResults($offset, $length)->toArray();
    }

    /**
     * Returns the number of results.
     *
     * @return int The number of results
     */
    public function getNbResults()
    {
        return $this->adapter->getTotalHits();
    }
}
