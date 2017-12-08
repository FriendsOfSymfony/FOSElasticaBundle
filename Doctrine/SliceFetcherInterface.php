<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
     * @param object $queryBuilder
     * @param int    $limit
     * @param int    $offset
     * @param array  $previousSlice
     * @param array  $identifierFieldNames
     *
     * @return array
     */
    public function fetch($queryBuilder, $limit, $offset, array $previousSlice, array $identifierFieldNames);
}
