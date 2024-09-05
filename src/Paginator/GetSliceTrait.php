<?php

namespace FOS\ElasticaBundle\Paginator;

trait GetSliceTrait
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
}
