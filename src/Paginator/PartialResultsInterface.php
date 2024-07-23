<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Paginator;

interface PartialResultsInterface
{
    /**
     * Returns the paginated results.
     *
     * @return array
     */
    public function toArray(): array;

    /**
     * Returns the number of results.
     *
     * @return int The number of results
     */
    public function getTotalHits(): int;

    /**
     * Returns the aggregations.
     *
     * @return array
     */
    public function getAggregations(): array;
}
