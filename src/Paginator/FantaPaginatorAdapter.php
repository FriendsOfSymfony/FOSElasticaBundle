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

use Pagerfanta\Adapter\AdapterInterface;

/**
 * @template T
 * @implements AdapterInterface<T>
 */
class FantaPaginatorAdapter implements AdapterInterface
{
    /**
     * @var PaginatorAdapterInterface<T>
     * @phpstan-ignore-next-line todo: make PaginatorAdapterInterface generic
     */
    private $adapter;

    /**
     * @param PaginatorAdapterInterface<T> $adapter
     * @phpstan-ignore-next-line todo: make PaginatorAdapterInterface generic
     */
    public function __construct(PaginatorAdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Returns the number of results.
     */
    public function getNbResults(): int
    {
        return $this->adapter->getTotalHits();
    }

    /**
     * Returns Aggregations.
     *
     * @return array<string, mixed>
     *
     * @api
     */
    public function getAggregations()
    {
        return $this->adapter->getAggregations();
    }

    /**
     * Returns Suggestions.
     *
     * @return array<string, mixed>
     *
     * @api
     */
    public function getSuggests()
    {
        return $this->adapter->getSuggests();
    }

    /**
     * Returns a slice of the results.
     *
     * @param int $offset The offset
     * @param int $length The length
     */
    public function getSlice($offset, $length): iterable
    {
        return $this->adapter->getResults($offset, $length)->toArray();
    }

    /**
     * @return float
     */
    public function getMaxScore()
    {
        return $this->adapter->getMaxScore();
    }
}
