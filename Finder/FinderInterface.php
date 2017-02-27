<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Finder;

interface FinderInterface
{
    /**
     * Searches for query results within a given limit.
     *
     * @param mixed $query   Can be a string, an array or an \Elastica\Query object
     * @param int   $limit   How many results to get
     * @param array $options
     *
     * @return array results
     */
    public function find($query, $limit = null, $options = array());
}
