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

use Elastica\Result;
use Elastica\ResultSet;

/**
 * Raw partial results transforms to a simple array.
 */
class RawPartialResults implements PartialResultsInterface
{
    public function __construct(protected ResultSet $resultSet)
    {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function toArray(): array
    {
        return \array_map(static fn (Result $result): array => $result->getSource(), $this->resultSet->getResults());
    }

    public function getTotalHits(): int
    {
        return $this->resultSet->getTotalHits();
    }

    public function getAggregations(): array
    {
        return $this->resultSet->getAggregations();
    }

    /**
     * @return array<string, mixed>
     */
    public function getSuggests(): array
    {
        return $this->resultSet->getSuggests();
    }
}
