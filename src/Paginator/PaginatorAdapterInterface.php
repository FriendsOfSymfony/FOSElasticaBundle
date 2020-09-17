<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Paginator;

interface PaginatorAdapterInterface
{
    /**
     * Returns the number of results.
     *
     * @return int The number of results
     */
    public function getTotalHits();

    /**
     * Returns an slice of the results.
     *
     * @param int $offset The offset
     * @param int $length The length
     *
     * @return PartialResultsInterface
     */
    public function getResults($offset, $length);

    /**
     * Returns Aggregations.
     *
     * @return mixed
     */
    public function getAggregations();

    /**
     * Returns Suggests.
     *
     * @return mixed
     */
    public function getSuggests();

    /**
     * Returns the max score.
     *
     * @return float
     */
    public function getMaxScore();
}
