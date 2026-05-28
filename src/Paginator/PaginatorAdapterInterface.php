<?php

declare(strict_types=1);

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
     */
    public function getTotalHits(): int;

    /**
     * Returns an slice of the results.
     */
    public function getResults(int $offset, int $length): PartialResultsInterface;

    /**
     * Returns Aggregations.
     *
     * @return array<string, mixed>
     */
    public function getAggregations(): array;

    /**
     * Returns Suggests.
     *
     * @return array<string, mixed>
     */
    public function getSuggests(): array;

    /**
     * Returns the max score.
     */
    public function getMaxScore(): float;
}
