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

/**
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
