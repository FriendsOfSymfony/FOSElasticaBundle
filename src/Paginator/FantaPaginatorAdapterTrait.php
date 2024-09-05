<?php

namespace FOS\ElasticaBundle\Paginator;

trait FantaPaginatorAdapterTrait
{
    /**
     * Returns a slice of the results.
     *
     * @param int $offset The offset
     * @param int $length The length
     *
     * @return iterable The slice
     */
    public function getSlice(int $offset, int $length): iterable
    {
        return $this->adapter->getResults($offset, $length)->toArray();
    }

    /**
     * Returns the number of results.
     *
     * @return int The number of results
     */
    public function getNbResults(): int
    {
        return $this->adapter->getTotalHits();
    }
}
