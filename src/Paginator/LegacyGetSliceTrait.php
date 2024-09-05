<?php

namespace FOS\ElasticaBundle\Paginator;

trait LegacyGetSliceTrait
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
}
