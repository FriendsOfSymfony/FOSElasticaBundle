<?php

namespace FOS\ElasticaBundle\Doctrine;

@trigger_error(sprintf('The %s class is deprecated since version 4.1 and will be removed in 5.0.', SliceFetcherInterface::class), E_USER_DEPRECATED);

/**
 * @deprecated since 4.1 will be removed in 5.x.
 *
 * Fetches a slice of objects.
 *
 * @author Thomas Prelot <tprelot@gmail.com>
 */
interface SliceFetcherInterface
{
    /**
     * Fetches a slice of objects using the query builder.
     *
     * @param object  $queryBuilder
     * @param integer $limit
     * @param integer $offset
     * @param array   $previousSlice
     * @param array   $identifierFieldNames
     *
     * @return array
     */
    public function fetch($queryBuilder, $limit, $offset, array $previousSlice, array $identifierFieldNames);
}
