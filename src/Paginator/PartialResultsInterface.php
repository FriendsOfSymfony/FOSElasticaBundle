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

interface PartialResultsInterface
{
    /**
     * Returns the paginated results.
     *
     * @return array<mixed>
     */
    public function toArray(): array;

    /**
     * Returns the number of results.
     */
    public function getTotalHits(): int;

    /**
     * Returns the aggregations.
     *
     * @return array<string, mixed>
     */
    public function getAggregations(): array;
}
