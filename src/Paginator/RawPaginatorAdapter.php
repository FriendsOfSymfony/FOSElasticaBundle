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

use Elastica\Query;
use Elastica\ResultSet;
use Elastica\SearchableInterface;

/**
 * Allows pagination of Elastica\Query. Does not map results.
 */
class RawPaginatorAdapter implements PaginatorAdapterInterface
{
    private ?int $totalHits = null;

    /**
     * @var array<string, mixed>|null for the aggregations
     */
    private ?array $aggregations = null;

    /**
     * @var array<string, mixed>|null for the suggesters
     */
    private ?array $suggests = null;

    private ?float $maxScore = null;

    /**
     * @see PaginatorAdapterInterface::__construct
     *
     * @param SearchableInterface  $searchable the object to search in
     * @param Query                $query      the query to search
     * @param array<string, mixed> $options
     */
    public function __construct(private readonly SearchableInterface $searchable, private readonly Query $query, private readonly array $options = [])
    {
    }

    public function getResults(int $offset, int $itemCountPerPage): RawPartialResults
    {
        return new RawPartialResults($this->getElasticaResults($offset, $itemCountPerPage));
    }

    /**
     * Returns the number of results.
     *
     * If genuineTotal is provided as true, total hits is returned from the
     * `hits.total` value from the search results instead of just returning
     * the requested size.
     */
    public function getTotalHits(bool $genuineTotal = false): int
    {
        if (null === $this->totalHits) {
            $this->totalHits = $this->searchable->count($this->query);
        }

        return $this->query->hasParam('size') && !$genuineTotal
            ? \min($this->totalHits, (int) $this->query->getParam('size'))
            : $this->totalHits;
    }

    public function getAggregations(): array
    {
        if (null === $this->aggregations) {
            $this->aggregations = $this->searchable->search($this->query)->getAggregations();
        }

        return $this->aggregations;
    }

    public function getSuggests(): array
    {
        if (null === $this->suggests) {
            $this->suggests = $this->searchable->search($this->query)->getSuggests();
        }

        return $this->suggests;
    }

    public function getMaxScore(): float
    {
        if (null === $this->maxScore) {
            $this->maxScore = $this->searchable->search($this->query)->getMaxScore();
        }

        return $this->maxScore;
    }

    /**
     * Returns the Query.
     */
    public function getQuery(): Query
    {
        return $this->query;
    }

    /**
     * Returns the paginated results.
     *
     * @throws \InvalidArgumentException
     */
    protected function getElasticaResults(int $offset, int $itemCountPerPage): ResultSet
    {
        $size = $this->query->hasParam('size')
            ? (int) $this->query->getParam('size')
            : null;

        if (null !== $size && $size < $offset + $itemCountPerPage) {
            $itemCountPerPage = $size - $offset;
        }

        if ($itemCountPerPage < 1) {
            throw new \InvalidArgumentException('$itemCountPerPage must be greater than zero');
        }

        $query = clone $this->query;
        $query->setFrom($offset);
        $query->setSize($itemCountPerPage);

        $resultSet = $this->searchable->search($query, $this->options);
        $this->totalHits = $resultSet->getTotalHits();
        $this->aggregations = $resultSet->getAggregations();
        $this->suggests = $resultSet->getSuggests();
        $this->maxScore = $resultSet->getMaxScore();

        return $resultSet;
    }
}
